<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Notifications\AdminCustomerNotification;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        // withTrashed() so blocked customers appear and can be filtered
        $query = User::withTrashed()
            ->where('user_type', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'blocked') {
                $query->whereNotNull('deleted_at');
            }
        }

        // Filter by registration date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate(20);

        // Statistics
        $stats = [
            'total'          => User::withTrashed()->where('user_type', 'customer')->count(),
            'active'         => User::where('user_type', 'customer')->whereNull('deleted_at')->count(),
            'blocked'        => User::withTrashed()->where('user_type', 'customer')->whereNotNull('deleted_at')->count(),
            'new_today'      => User::withTrashed()->where('user_type', 'customer')->whereDate('created_at', today())->count(),
            'new_this_month' => User::withTrashed()->where('user_type', 'customer')->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * Show customer details
     */
    public function show(User $customer)
    {
        if ($customer->user_type !== 'customer') {
            abort(404);
        }

        $customer->load(['addresses', 'orders.items']);

        $stats = [
            'total_orders'     => $customer->orders()->count(),
            'completed_orders' => $customer->orders()->where('status', 'delivered')->count(),
            'total_spent'      => $customer->orders()->where('payment_status', 'paid')->sum('total'),
            'avg_order_value'  => $customer->orders()->where('payment_status', 'paid')->avg('total') ?? 0,
            'last_order_date'  => $customer->orders()->latest()->first()?->created_at,
        ];

        $recentOrders = $customer->orders()
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.customers.show', compact('customer', 'stats', 'recentOrders'));
    }

    /**
     * Block / Suspend customer (soft-delete)
     */
    public function block(Request $request, User $customer)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($customer->user_type !== 'customer') {
            abort(404);
        }

        $customer->delete();

        return back()->with('success', 'Customer has been blocked successfully.');
    }

    /**
     * Unblock customer.
     * Accepts raw $id because soft-deleted records won't resolve via
     * default route model binding.
     */
    public function unblock($id)
    {
        $customer = User::withTrashed()->findOrFail($id);

        if ($customer->user_type !== 'customer') {
            abort(404);
        }

        if (!$customer->trashed()) {
            return back()->with('info', 'Customer is not currently blocked.');
        }

        $customer->restore();

        return back()->with('success', 'Customer has been unblocked successfully.');
    }

    /**
     * Send an email notification to a customer from the admin panel.
     */
    public function notify(Request $request, User $customer)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($customer->user_type !== 'customer') {
            abort(404);
        }

        $admin     = auth()->guard('admin')->user();
        $adminName = $admin?->name ?? config('app.name') . ' Support';

        try {
            $customer->notify(
                new AdminCustomerNotification(
                    $request->subject,
                    $request->message,
                    $adminName
                )
            );

            return back()->with('success', 'Notification email sent successfully to ' . $customer->email . '.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Export customers to CSV
     */
    public function export(Request $request)
    {
        $query = User::withTrashed()
            ->where('user_type', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'blocked') {
                $query->whereNotNull('deleted_at');
            }
        }

        $customers = $query->get();

        $filename = 'customers_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Orders', 'Total Spent', 'Joined', 'Status']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone ?? 'N/A',
                    $customer->orders_count,
                    number_format($customer->total_spent ?? 0, 2),
                    $customer->created_at->format('Y-m-d'),
                    $customer->deleted_at ? 'Blocked' : 'Active',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * View all orders for a specific customer
     */
    public function orders(User $customer)
    {
        if ($customer->user_type !== 'customer') {
            abort(404);
        }

        $orders = $customer->orders()
            ->with('items')
            ->latest()
            ->paginate(20);

        return view('admin.customers.orders', compact('customer', 'orders'));
    }
}