<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\RiderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DeliveryPayoutController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin Authentication Routes (Guest only)
Route::prefix('admin')->name('admin.')->middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');
});

// Admin Protected Routes
Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
    
    // Logout
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    
    // Redirect /admin to dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::post('/customers/{customer}/block', [CustomerController::class, 'block'])->name('customers.block');
    Route::post('/customers/{customer}/unblock', [CustomerController::class, 'unblock'])->name('customers.unblock');
    Route::post('/customers/{customer}/notify', [CustomerController::class, 'notify'])->name('customers.notify');


    Route::get('/sellers', [SellerController::class, 'index'])->name('sellers.index');
    Route::get('/sellers/export', [SellerController::class, 'export'])->name('sellers.export');
    Route::get('/sellers/{seller}', [SellerController::class, 'show'])->name('sellers.show');
    Route::get('/sellers/applications/view', [SellerController::class, 'applications'])->name('sellers.applications');
    Route::post('/sellers/{seller}/approve', [SellerController::class, 'approve'])->name('sellers.approve');
    Route::post('/sellers/{seller}/suspend', [SellerController::class, 'suspend'])->name('sellers.suspend');
    Route::post('/sellers/{seller}/reject', [SellerController::class, 'reject'])->name('sellers.reject');
    Route::get('/sellers/{seller}/products', [SellerController::class, 'products'])->name('sellers.products');
    Route::get('/sellers/{seller}/wallet', [SellerController::class, 'wallet'])->name('sellers.wallet');
    Route::post('/sellers/{seller}/activate', [SellerController::class, 'activate'])->name('sellers.activate');
    Route::post('/sellers/{seller}/notify', [SellerController::class, 'notify'])->name('sellers.notify');
    Route::post('/sellers/{seller}/update-commission', [SellerController::class, 'updateCommission'])->name('sellers.update-commission');


    Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
    Route::get('/riders/map', [RiderController::class, 'map'])->name('riders.map');
    Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('riders.show');
    Route::get('/riders/{rider}/orders', [RiderController::class, 'orders'])->name('riders.orders');
    Route::get('/riders/applications', [RiderController::class, 'applications'])->name('riders.applications');
    Route::get('/riders/export', [RiderController::class, 'export'])->name('riders.export');
    Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
    Route::post('/riders/{rider}/suspend', [RiderController::class, 'suspend'])->name('riders.suspend');
    Route::post('/riders/{rider}/reject', [RiderController::class, 'reject'])->name('riders.reject');
    Route::get('/riders/{rider}/deliveries', [RiderController::class, 'deliveries'])->name('riders.deliveries');
    Route::get('/riders/{rider}/earnings', [RiderController::class, 'earnings'])->name('riders.earnings');

    Route::prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/payouts', [DeliveryPayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/{payout}', [DeliveryPayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/{payout}/approve', [DeliveryPayoutController::class, 'approve'])->name('payouts.approve');
    Route::post('/payouts/{payout}/pay', [DeliveryPayoutController::class, 'markAsPaid'])->name('payouts.pay');
    Route::post('/payouts/{payout}/reject', [DeliveryPayoutController::class, 'reject'])->name('payouts.reject');
    Route::get('/payouts/export', [DeliveryPayoutController::class, 'export'])->name('payouts.export');
    Route::post('/payouts/batch-approve', [DeliveryPayoutController::class, 'batchApprove'])->name('payouts.batch-approve');
});


    // Product Management
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('toggle-featured');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [ProductController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/export/csv', [ProductController::class, 'export'])->name('export');
});

// Category Management (keep as is)
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [CategoryController::class, 'reorder'])->name('reorder');
    
    // Subcategories
    Route::get('/{category}/subcategories/create', [CategoryController::class, 'createSubcategory'])->name('subcategories.create');
    Route::post('/{category}/subcategories', [CategoryController::class, 'storeSubcategory'])->name('subcategories.store');
    Route::get('/subcategories/{subcategory}/edit', [CategoryController::class, 'editSubcategory'])->name('subcategories.edit');
    Route::put('/subcategories/{subcategory}', [CategoryController::class, 'updateSubcategory'])->name('subcategories.update');
    Route::delete('/subcategories/{subcategory}', [CategoryController::class, 'destroySubcategory'])->name('subcategories.destroy');
});




// Order Management
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/analytics', [OrderController::class, 'analytics'])->name('analytics');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
    Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
    Route::post('/{order}/update-payment', [OrderController::class, 'updatePaymentStatus'])->name('update-payment');
    Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    Route::post('/bulk-action', [OrderController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/export/csv', [OrderController::class, 'export'])->name('export');
});


    



//Reports 
Route::prefix('reports')->name('reports.')->group(function () {
        
    // Main Analytics Dashboard
    Route::get('/', [ReportController::class, 'index'])
        ->name('index')
        ->middleware('permission:reports.view');
    
    // Revenue Analytics
    Route::get('/revenue', [ReportController::class, 'revenue'])
        ->name('revenue')
        ->middleware('permission:reports.view');
    
    // Sales Analytics
    Route::get('/sales', [ReportController::class, 'sales'])
        ->name('sales')
        ->middleware('permission:reports.view');
    
    // User Analytics
    Route::get('/users', [ReportController::class, 'users'])
        ->name('users')
        ->middleware('permission:reports.view');
    
    // Delivery Analytics
    Route::get('/deliveries', [ReportController::class, 'deliveries'])
        ->name('deliveries')
        ->middleware('permission:reports.view');
    
    // Product Analytics
    Route::get('/products', [ReportController::class, 'products'])
        ->name('products')
        ->middleware('permission:reports.view');
    
    // Commission Analytics
    Route::get('/commission', [ReportController::class, 'commission'])
        ->name('commission')
        ->middleware('permission:finance.view');
    
    // Export Report
    Route::post('/export', [ReportController::class, 'export'])
        ->name('export')
        ->middleware('permission:reports.export');
    
    // Custom Report Builder
    Route::get('/custom', [ReportController::class, 'custom'])
        ->name('custom')
        ->middleware('permission:reports.custom');
    
    Route::post('/custom/generate', [ReportController::class, 'generateCustom'])
        ->name('custom.generate')
        ->middleware('permission:reports.custom');
    
    // Schedule Report
    Route::post('/schedule', [ReportController::class, 'schedule'])
        ->name('schedule')
        ->middleware('permission:reports.schedule');
    
    // AJAX: Get Chart Data
    Route::get('/chart-data', [ReportController::class, 'chartData'])
        ->name('chart-data')
        ->middleware('permission:reports.view');
});



Route::prefix('companies')->name('companies.')->group(function () {
    // CRUD Operations
    Route::get('/', [CompanyController::class, 'index'])->name('index');
    Route::get('/create', [CompanyController::class, 'create'])->name('create');
    Route::post('/', [CompanyController::class, 'store'])->name('store');
    Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
    Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
    Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
    Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
    
    // Company Actions
    Route::post('/{company}/suspend', [CompanyController::class, 'suspend'])->name('suspend');
    Route::post('/{company}/activate', [CompanyController::class, 'activate'])->name('activate');
    
    // View Company Data
    Route::get('/{company}/deliveries', [CompanyController::class, 'deliveries'])->name('deliveries');
    Route::get('/{company}/earnings', [CompanyController::class, 'earnings'])->name('earnings');
    
    // Export
    Route::get('/export/companies', [CompanyController::class, 'export'])->name('export');
});




Route::prefix('deliveries')->name('deliveries.')->group(function () {
    // Main views
    Route::get('/', [DeliveryController::class, 'index'])->name('index');
    Route::get('/map', [DeliveryController::class, 'map'])->name('map');
    Route::get('/unassigned', [DeliveryController::class, 'unassigned'])->name('unassigned');
    Route::get('/failed', [DeliveryController::class, 'failed'])->name('failed');
    
    // Live data (AJAX)
    Route::get('/live-data', [DeliveryController::class, 'liveData'])->name('liveData');
    
    // Single delivery
    Route::get('/{delivery}', [DeliveryController::class, 'show'])->name('show');
    
    // Assignment
    Route::get('/{delivery}/assign', [DeliveryController::class, 'assignPage'])->name('assignPage');
    Route::post('/{delivery}/assign', [DeliveryController::class, 'assign'])->name('assign');
    
    // Broadcasting
    Route::get('/{delivery}/broadcast', [DeliveryController::class, 'broadcastPage'])->name('broadcastPage');
    Route::post('/{delivery}/broadcast', [DeliveryController::class, 'sendBroadcast'])->name('sendBroadcast');
    
    // Actions
    Route::post('/{delivery}/reassign', [DeliveryController::class, 'reassign'])->name('reassign');
    Route::post('/{delivery}/update-status', [DeliveryController::class, 'updateStatus'])->name('updateStatus');
    Route::post('/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('cancel');
});

// ============================================
// FINANCIAL MANAGEMENT - PAYOUTS
// ============================================
Route::prefix('finance/payouts')->name('finance.payouts.')->group(function () {
    // List & View
    Route::get('/', [PayoutController::class, 'index'])->name('index');
    Route::get('/{payout}', [PayoutController::class, 'show'])->name('show');
    
    // Actions
    Route::post('/{payout}/approve', [PayoutController::class, 'approve'])->name('approve');
    Route::post('/{payout}/complete', [PayoutController::class, 'complete'])->name('complete');
    Route::post('/{payout}/reject', [PayoutController::class, 'reject'])->name('reject');
    
    // Bulk operations
    Route::post('/bulk-approve', [PayoutController::class, 'bulkApprove'])->name('bulkApprove');
    
    // Analytics & Export
    Route::get('/analytics/overview', [PayoutController::class, 'analytics'])->name('analytics');
    Route::get('/export/csv', [PayoutController::class, 'export'])->name('export');
});

// ============================================
// FINANCIAL MANAGEMENT - WALLETS
// ============================================
Route::prefix('finance/wallets')->name('finance.wallets.')->group(function () {
    // List & View
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/{wallet}', [WalletController::class, 'show'])->name('show');
    
    // Manual Adjustments
    Route::get('/{wallet}/adjust', [WalletController::class, 'adjustPage'])->name('adjustPage');
    Route::post('/{wallet}/adjust', [WalletController::class, 'adjust'])->name('adjust');
    
    // Release Pending
    Route::post('/{wallet}/release-pending', [WalletController::class, 'releasePending'])->name('releasePending');
    
    // Transaction Details
    Route::get('/transaction/{transaction}', [WalletController::class, 'transaction'])->name('transaction');
    
    // Analytics & Export
    Route::get('/analytics/overview', [WalletController::class, 'analytics'])->name('analytics');
    Route::get('/export/transactions', [WalletController::class, 'exportTransactions'])->name('exportTransactions');
});

// ============================================
// FINANCIAL MANAGEMENT - REFUNDS
// ============================================
Route::prefix('finance/refunds')->name('finance.refunds.')->group(function () {
    // List & View
    Route::get('/', [RefundController::class, 'index'])->name('index');
    Route::get('/{order}', [RefundController::class, 'show'])->name('show');
    
    // Actions
    Route::post('/{order}/process', [RefundController::class, 'process'])->name('process');
    Route::post('/{order}/reject', [RefundController::class, 'reject'])->name('reject');
});

// ============================================
// FINANCIAL MANAGEMENT - REPORTS
// ============================================
Route::prefix('finance/reports')->name('finance.reports.')->group(function () {
    // Main Dashboard
    Route::get('/', [FinancialReportController::class, 'index'])->name('index');
    
    // Specific Reports
    Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cashFlow');
    
    // Export
    Route::get('/export', [FinancialReportController::class, 'export'])->name('export');
});


// ============================================
// ADMIN REVIEW ROUTES
// ============================================

// Admin Review Management
Route::prefix('reviews')->name('reviews.')->group(function () {
    // List & View
    Route::get('/', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('index');
    Route::get('/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('show');
    
    // Actions
    Route::post('/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('approve');
    Route::post('/{review}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reject');
    Route::post('/{review}/toggle-status', [App\Http\Controllers\Admin\ReviewController::class, 'toggleStatus'])->name('toggleStatus');
    
    // Bulk Actions
    Route::post('/bulk-approve', [App\Http\Controllers\Admin\ReviewController::class, 'bulkApprove'])->name('bulkApprove');
    
    // Update & Delete
    Route::put('/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('destroy');
    
    // Analytics & Export
    Route::get('/analytics/overview', [App\Http\Controllers\Admin\ReviewController::class, 'analytics'])->name('analytics');
    Route::get('/export/csv', [App\Http\Controllers\Admin\ReviewController::class, 'export'])->name('export');
});

 Route::prefix('settings/telegram')->name('admin.telegram.')->group(function () {
        Route::get('/',                     [AdminTelegramController::class, 'index'])->name('index');
        Route::post('/register/{admin}',    [AdminTelegramController::class, 'register'])->name('register');
        Route::delete('/unregister/{admin}',[AdminTelegramController::class, 'unregister'])->name('unregister');
        Route::post('/test/{admin}',        [AdminTelegramController::class, 'sendTest'])->name('test');
        Route::put('/preferences/{admin}',  [AdminTelegramController::class, 'updatePreferences'])->name('preferences');
 });

// ============================================
// USER MANAGEMENT (Placeholder for future)
// ============================================
Route::prefix('users')->name('users.')->group(function () {
    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        // Route::get('/', [CustomerController::class, 'index'])->name('index');
        // Route::get('/{user}', [CustomerController::class, 'show'])->name('show');
    });
    
    // Sellers
    Route::prefix('sellers')->name('sellers.')->group(function () {
        // Route::get('/', [SellerController::class, 'index'])->name('index');
        // Route::get('/{seller}', [SellerController::class, 'show'])->name('show');
        // Route::get('/applications', [SellerController::class, 'applications'])->name('applications');
        // Route::post('/{seller}/approve', [SellerController::class, 'approve'])->name('approve');
        // Route::post('/{seller}/reject', [SellerController::class, 'reject'])->name('reject');
    });
    
    // Riders
    Route::prefix('riders')->name('riders.')->group(function () {
        // Route::get('/', [RiderController::class, 'index'])->name('index');
        // Route::get('/{rider}', [RiderController::class, 'show'])->name('show');
        // Route::get('/applications', [RiderController::class, 'applications'])->name('applications');
        // Route::post('/{rider}/approve', [RiderController::class, 'approve'])->name('approve');
        // Route::post('/{rider}/reject', [RiderController::class, 'reject'])->name('reject');
    });
});

// ============================================
// PRODUCT MANAGEMENT (Placeholder for future)
// ============================================
Route::prefix('products')->name('products.')->group(function () {
    // Route::get('/', [ProductController::class, 'index'])->name('index');
    // Route::get('/pending', [ProductController::class, 'pending'])->name('pending');
    // Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    // Route::post('/{product}/approve', [ProductController::class, 'approve'])->name('approve');
    // Route::post('/{product}/reject', [ProductController::class, 'reject'])->name('reject');
});

// ============================================
// CATEGORY MANAGEMENT (Placeholder for future)
// ============================================
Route::prefix('categories')->name('categories.')->group(function () {
    // Route::get('/', [CategoryController::class, 'index'])->name('index');
    // Route::get('/create', [CategoryController::class, 'create'])->name('create');
    // Route::post('/', [CategoryController::class, 'store'])->name('store');
    // Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    // Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    // Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

// ============================================
// ORDER MANAGEMENT (Placeholder for future)
// ============================================
Route::prefix('orders')->name('orders.')->group(function () {
    // Route::get('/', [OrderController::class, 'index'])->name('index');
    // Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    // Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('updateStatus');
});

// ============================================
// SETTINGS (Placeholder for future)
// ============================================
Route::prefix('settings')->name('settings.')->group(function () {
    // Route::get('/general', [SettingsController::class, 'general'])->name('general');
    // Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
    
    // Route::get('/commission', [SettingsController::class, 'commission'])->name('commission');
    // Route::post('/commission', [SettingsController::class, 'updateCommission'])->name('commission.update');
    
    // Route::get('/payment', [SettingsController::class, 'payment'])->name('payment');
    // Route::post('/payment', [SettingsController::class, 'updatePayment'])->name('payment.update');
    
    // Route::get('/delivery', [SettingsController::class, 'delivery'])->name('delivery');
    // Route::post('/delivery', [SettingsController::class, 'updateDelivery'])->name('delivery.update');
    
    // Route::get('/email-templates', [SettingsController::class, 'emailTemplates'])->name('emailTemplates');
    // Route::post('/email-templates', [SettingsController::class, 'updateEmailTemplate'])->name('emailTemplates.update');
});

// ============================================
// NOTIFICATIONS (Placeholder for future)
// ============================================
Route::prefix('notifications')->name('notifications.')->group(function () {
    // Route::get('/', [NotificationController::class, 'index'])->name('index');
    // Route::post('/send', [NotificationController::class, 'send'])->name('send');
    // Route::post('/bulk-send', [NotificationController::class, 'bulkSend'])->name('bulkSend');
});

// ============================================
// SUPPORT & TICKETS (Placeholder for future)
// ============================================
Route::prefix('support')->name('support.')->group(function () {
    // Route::get('/tickets', [SupportController::class, 'tickets'])->name('tickets');
    // Route::get('/tickets/{ticket}', [SupportController::class, 'show'])->name('tickets.show');
    // Route::post('/tickets/{ticket}/reply', [SupportController::class, 'reply'])->name('tickets.reply');
    // Route::post('/tickets/{ticket}/close', [SupportController::class, 'close'])->name('tickets.close');
});

// ============================================
// LOGS & AUDIT TRAIL (Placeholder for future)
// ============================================
Route::prefix('logs')->name('logs.')->group(function () {
    // Route::get('/activity', [LogController::class, 'activity'])->name('activity');
    // Route::get('/errors', [LogController::class, 'errors'])->name('errors');
    // Route::get('/audit', [LogController::class, 'audit'])->name('audit');
});

// ============================================
// ANALYTICS (Placeholder for future)
// ============================================
Route::prefix('analytics')->name('analytics.')->group(function () {
    // Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
    // Route::get('/sales', [AnalyticsController::class, 'sales'])->name('sales');
    // Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
    // Route::get('/deliveries', [AnalyticsController::class, 'deliveries'])->name('deliveries');
});


    // More routes will be added here in future deliverables
});