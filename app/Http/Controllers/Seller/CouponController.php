<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    private function sellerShop()
    {
        return Auth::guard('seller')->user()->seller->shop;
    }

  private function sellerId(): int
{
    return Auth::guard('seller')->user()->seller->id;
}

    // ----------------------------------------------------------------
    // INDEX
    // ----------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Coupon::forSeller($this->sellerId())
            ->with('product:id,name')
            ->latest();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $coupons  = $query->paginate(15)->withQueryString();
        $products = $this->sellerShop()
            ->products()
            ->select('id', 'name')
            ->active()
            ->orderBy('name')
            ->get();

        return view('seller.coupons.index', compact('coupons', 'products'));
    }

    // ----------------------------------------------------------------
    // CREATE
    // ----------------------------------------------------------------
    public function create()
    {
        $products = $this->sellerShop()
            ->products()
            ->select('id', 'name')
            ->active()
            ->orderBy('name')
            ->get();

        $suggestedCode = Coupon::generateCode();

        return view('seller.coupons.create', compact('products', 'suggestedCode'));
    }

    // ----------------------------------------------------------------
    // STORE
    // ----------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'               => 'required|string|max:50|unique:coupons,code',
            'description'        => 'nullable|string|max:255',
            'product_id'         => 'nullable|exists:products,id',
            'type'               => 'required|in:percent,fixed',
            'value'              => 'required|numeric|min:0.01',
            'min_order_amount'   => 'nullable|numeric|min:0',
            'max_discount_amount'=> 'nullable|numeric|min:0',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'required|integer|min:1',
            'starts_at'          => 'nullable|date',
            'expires_at'         => 'nullable|date|after_or_equal:starts_at',
            'is_active'          => 'boolean',
        ]);

        // Extra validation: percent value can't exceed 100
        if ($validated['type'] === 'percent' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        // Verify product belongs to this seller
        if (!empty($validated['product_id'])) {
            $belongs = $this->sellerShop()
                ->products()
                ->where('id', $validated['product_id'])
                ->exists();

            if (!$belongs) {
                return back()->withErrors(['product_id' => 'Selected product does not belong to your shop.'])->withInput();
            }
        }

        Coupon::create(array_merge($validated, [
            'seller_id'        => $this->sellerId(),
            'is_active'        => $request->boolean('is_active', true),
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
        ]));

        return redirect()->route('seller.coupons.index')
            ->with('success', 'Coupon created successfully!');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $products = $this->sellerShop()
            ->products()
            ->select('id', 'name')
            ->active()
            ->orderBy('name')
            ->get();

        return view('seller.coupons.edit', compact('coupon', 'products'));
    }

    // ----------------------------------------------------------------
    // UPDATE
    // ----------------------------------------------------------------
    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $validated = $request->validate([
            'code'               => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description'        => 'nullable|string|max:255',
            'product_id'         => 'nullable|exists:products,id',
            'type'               => 'required|in:percent,fixed',
            'value'              => 'required|numeric|min:0.01',
            'min_order_amount'   => 'nullable|numeric|min:0',
            'max_discount_amount'=> 'nullable|numeric|min:0',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'required|integer|min:1',
            'starts_at'          => 'nullable|date',
            'expires_at'         => 'nullable|date|after_or_equal:starts_at',
            'is_active'          => 'boolean',
        ]);

        if ($validated['type'] === 'percent' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        if (!empty($validated['product_id'])) {
            $belongs = $this->sellerShop()
                ->products()
                ->where('id', $validated['product_id'])
                ->exists();

            if (!$belongs) {
                return back()->withErrors(['product_id' => 'Selected product does not belong to your shop.'])->withInput();
            }
        }

        $coupon->update(array_merge($validated, [
            'is_active'        => $request->boolean('is_active', true),
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
        ]));

        return redirect()->route('seller.coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    // ----------------------------------------------------------------
    // DESTROY
    // ----------------------------------------------------------------
    public function destroy(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);
        $coupon->delete();

        return redirect()->route('seller.coupons.index')
            ->with('success', 'Coupon deleted.');
    }

    // ----------------------------------------------------------------
    // TOGGLE ACTIVE STATUS (AJAX)
    // ----------------------------------------------------------------
    public function toggleStatus(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $coupon->is_active,
            'message'   => $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.',
        ]);
    }

    // ----------------------------------------------------------------
    // PRIVATE
    // ----------------------------------------------------------------
    private function authorizeCoupon(Coupon $coupon): void
    {
        abort_unless($coupon->seller_id === $this->sellerId(), 403);
    }
}