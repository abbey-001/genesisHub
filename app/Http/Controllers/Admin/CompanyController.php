<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display all delivery companies
     */
    public function index(Request $request)
    {
        $query = Rider::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_verified', false);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $companies = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => Rider::count(),
            'active' => Rider::where('is_active', true)->where('is_verified', true)->count(),
            'suspended' => Rider::where('is_active', false)->count(),
            'pending_verification' => Rider::where('is_verified', false)->count(),
        ];

        return view('admin.companies.index', compact('companies', 'stats'));
    }

    /**
     * Show create company form
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store new company
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:20',
            'vehicle_type' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'account_name' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create user account
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'rider', // Companies use rider role
                'user_type' => 'rider', // Set user_type for companies
            ]);

            // Create company (rider) record
            $company = Rider::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'vehicle_type' => $validated['vehicle_type'],
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_name' => $validated['account_name'] ?? null,
                'is_verified' => true,
                'is_active' => true,
                'completed_deliveries' => 0,
                'failed_deliveries' => 0,
            ]);

            DB::commit();

            return redirect()->route('admin.companies.index')
                ->with('success', 'Delivery company created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create company: ' . $e->getMessage());
        }
    }

    /**
     * Show company details
     */
    public function show(Rider $company)
    {
        $company->load('user');

        // Company statistics
        $stats = [
            'total_deliveries' => $company->deliveries()->count(),
            'completed' => $company->completed_deliveries,
            'failed' => $company->failed_deliveries,
            'active' => $company->activeDeliveries()->count(),
            'total_earnings' => $company->deliveries()
                ->where('status', 'delivered')
                ->sum('delivery_fee'),
            'success_rate' => $company->success_rate,
        ];

        // Recent deliveries
        $recentDeliveries = $company->deliveries()
            ->with('order')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.companies.show', compact('company', 'stats', 'recentDeliveries'));
    }

    /**
     * Show edit company form
     */
    public function edit(Rider $company)
    {
        $company->load('user');
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update company
     */
    public function update(Request $request, Rider $company)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($company->user_id)
            ],
            'password' => 'nullable|string|min:8',
            'phone_number' => 'required|string|max:20',
            'vehicle_type' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'account_name' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Update user account
            $updateData = [
                'name' => $validated['full_name'],
                'email' => $validated['email'],
            ];

            if ($validated['password']) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $company->user->update($updateData);

            // Update company record
            $company->update([
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'vehicle_type' => $validated['vehicle_type'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
            ]);

            DB::commit();

            return redirect()->route('admin.companies.show', $company)
                ->with('success', 'Company updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update company: ' . $e->getMessage());
        }
    }

    /**
     * Suspend company
     */
    public function suspend(Request $request, Rider $company)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $company->update([
                'is_active' => false,
            ]);

            // Reassign active deliveries if any
            $activeDeliveries = $company->activeDeliveries()->get();
            foreach ($activeDeliveries as $delivery) {
                $delivery->update([
                    'rider_id' => null,
                    'status' => 'pending',
                ]);
                
                // Re-broadcast to other companies
                // TODO: Implement re-broadcasting logic
            }

            DB::commit();

            return back()->with('success', 'Company suspended and active deliveries reassigned.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to suspend company: ' . $e->getMessage());
        }
    }

    /**
     * Activate company
     */
    public function activate(Rider $company)
    {
        $company->update([
            'is_active' => true,
            'is_verified' => true,
        ]);

        return back()->with('success', 'Company has been activated.');
    }

    /**
     * View company's deliveries
     */
    public function deliveries(Rider $company)
    {
        $deliveries = $company->deliveries()
            ->with('order')
            ->latest()
            ->paginate(20);

        return view('admin.companies.deliveries', compact('company', 'deliveries'));
    }

    /**
     * View company's earnings
     */
    public function earnings(Rider $company)
    {
        $deliveries = $company->deliveries()
            ->where('status', 'delivered')
            ->with('order')
            ->latest('delivered_at')
            ->paginate(20);

        $totalEarnings = $company->deliveries()
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        $earningsByMonth = $company->deliveries()
            ->where('status', 'delivered')
            ->selectRaw('DATE_FORMAT(delivered_at, "%Y-%m") as month, SUM(delivery_fee) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return view('admin.companies.earnings', compact('company', 'deliveries', 'totalEarnings', 'earningsByMonth'));
    }

    /**
     * Export companies
     */
    public function export(Request $request)
    {
        $query = Rider::with('user');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        $companies = $query->get();

        $filename = 'delivery_companies_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($companies) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'Company Name', 'Email', 'Phone', 'Fleet Type', 'Status', 'Deliveries', 'Success Rate', 'Joined']);
            
            foreach ($companies as $company) {
                fputcsv($file, [
                    $company->id,
                    $company->full_name,
                    $company->user->email,
                    $company->phone_number,
                    $company->vehicle_type ?? 'N/A',
                    $company->is_active ? 'Active' : 'Suspended',
                    $company->completed_deliveries,
                    $company->success_rate . '%',
                    $company->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete company (soft delete)
     */
    public function destroy(Rider $company)
    {
        if ($company->activeDeliveries()->count() > 0) {
            return back()->with('error', 'Cannot delete company with active deliveries.');
        }

        DB::beginTransaction();
        try {
            // Deactivate instead of deleting
            $company->update([
                'is_active' => false,
                'is_verified' => false,
            ]);

            $company->user->update([
                'email' => $company->user->email . '_deleted_' . time(),
            ]);

            DB::commit();

            return redirect()->route('admin.companies.index')
                ->with('success', 'Company deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete company: ' . $e->getMessage());
        }
    }
}