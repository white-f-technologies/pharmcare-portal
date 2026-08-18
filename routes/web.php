<?php

use App\Http\Controllers\Api\PortalApiController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosticsController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!is_setup_complete()) {
        return redirect()->route('setup.index');
    }
    return redirect()->route('login');
});

// Dynamic file server for public media assets (guarantees logo & images load in standalone mode)
Route::get('media/{path}', function (string $path) {
    $baseDir = realpath(storage_path('app/public'));
    $targetPath = storage_path('app/public/' . ltrim($path, '/\\'));
    $realPath = realpath($targetPath);

    if (!$baseDir || !$realPath || !str_starts_with($realPath, $baseDir . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
        abort(404);
    }
    return response()->file($realPath);
})->where('path', '.*')->name('media.serve');

Route::get('storage/{path}', function (string $path) {
    $baseDir = realpath(storage_path('app/public'));
    $targetPath = storage_path('app/public/' . ltrim($path, '/\\'));
    $realPath = realpath($targetPath);

    if (!$baseDir || !$realPath || !str_starts_with($realPath, $baseDir . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
        abort(404);
    }
    return response()->file($realPath);
})->where('path', '.*')->name('storage.serve');

Route::middleware('guest')->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
});

Route::middleware(['auth', 'verified', 'setup'])->group(function () {

    // Common routes for all active roles (Admin, Pharmacist, Cashier)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [DashboardController::class, 'liveData'])->name('dashboard.live');

    // Customer Returns routes (must be defined BEFORE Route::resource('sales') so 'returns' is not matched as {sale} ID parameter)
    Route::middleware('role:admin,pharmacist')->group(function () {
        Route::get('sales/returns', [SaleReturnController::class, 'index'])->name('sales.returns.index');
        Route::get('sales/{sale}/return', [SaleReturnController::class, 'create'])->name('sales.returns.create');
        Route::post('sales/returns', [SaleReturnController::class, 'store'])->name('sales.returns.store');
    });

    // POS & Sales management
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');

    // Customers management (for POS & customer tracking - available to all roles)
    Route::patch('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::resource('customers', CustomerController::class);

    // Read-only catalog & prescription index for all roles
    Route::get('medicines/search', [MedicineController::class, 'search'])->name('medicines.search');
    Route::get('medicines', [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');

    // Admin & Pharmacist routes (Inventory management, Purchases, Prescriptions creation, Reports)
    Route::middleware('role:admin,pharmacist')->group(function () {
        // Inventory management (Create, Edit, Delete medicines & batches)
        Route::get('medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('medicines', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('medicines/{medicine}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::delete('medicines/{medicine}', [MedicineController::class, 'destroy'])->name('medicines.destroy');
        Route::resource('medicines.batches', BatchController::class)->shallow();

        // Stock Adjustments & Damage routes
        Route::get('stock/adjustments', [StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
        Route::get('stock/adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock.adjustments.create');
        Route::post('stock/adjustments', [StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');

        // Categories & Suppliers management
        Route::resource('categories', CategoryController::class);
        Route::post('suppliers/quick-store', [SupplierController::class, 'quickStore'])->name('suppliers.quick-store');
        Route::resource('suppliers', SupplierController::class);

        // Expense Management & Expense Categories
        Route::resource('expenses', ExpenseController::class);
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['create', 'show', 'edit']);

        // Purchases management
        Route::resource('purchases', PurchaseController::class);

        // Prescription creation & editing
        Route::get('prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::get('prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::put('prescriptions/{prescription}', [PrescriptionController::class, 'update'])->name('prescriptions.update');
        Route::delete('prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');

        // Reports
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/expiry', [ReportController::class, 'expiry'])->name('reports.expiry');
        Route::get('reports/ledger', [StockLedgerController::class, 'index'])->name('reports.ledger');
    });

    // Parameterized wildcard show routes (placed AFTER create routes to prevent {medicine} matching 'create')
    Route::get('medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');
    Route::get('prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

    // Admin-only routes (User Management, System Settings & Database Backups)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::get('/settings/index', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('/settings/logo', [SettingController::class, 'removeLogo'])->name('settings.logo.remove');

        // Client License Management (Status View & Import/Activate)
        Route::get('/settings/license', [LicenseController::class, 'index'])->name('settings.license');
        Route::post('/settings/license', [LicenseController::class, 'activate'])->name('settings.license.activate');

        // Database Backup Management
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('/backups/upload-restore', [BackupController::class, 'uploadRestore'])->name('backups.upload-restore');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // System Diagnostics
        Route::get('/admin/diagnostics', [DiagnosticsController::class, 'index'])->name('admin.diagnostics');

        // Vendor Control Center (PharmCare Vendor Portal - strictly available when vendor_mode or private.key exists)
        if (config('app.vendor_mode', false) || file_exists(storage_path('keys/private.key'))) {
            Route::prefix('vendor-portal')->name('vendor.')->group(function () {
                Route::get('/', [VendorPortalController::class, 'dashboard'])->name('dashboard');
                Route::get('/clients', [VendorPortalController::class, 'clients'])->name('clients');
                Route::post('/clients', [VendorPortalController::class, 'storeClient'])->name('clients.store');
                Route::get('/installations', [VendorPortalController::class, 'installations'])->name('installations');
                Route::get('/releases', [VendorPortalController::class, 'releases'])->name('releases');
                Route::post('/releases', [VendorPortalController::class, 'storeRelease'])->name('releases.store');
                Route::get('/license-generator', [LicenseController::class, 'generator'])->name('license.generator');
                Route::post('/license-generator', [LicenseController::class, 'generate'])->name('license.generate');
            });
        }
    });
});

// Vendor Management Public API Endpoints (called by client desktop apps)
Route::prefix('api/v1')->middleware('throttle:60,1')->group(function () {
    Route::post('/license/activate', [PortalApiController::class, 'activate']);
    Route::post('/license/verify', [PortalApiController::class, 'verify']);
    Route::get('/releases/latest', [PortalApiController::class, 'latestRelease']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
