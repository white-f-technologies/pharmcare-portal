<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!is_setup_complete()) {
        return redirect()->route('setup.index');
    }
    return redirect()->route('login');
});

// Dynamic file server for public media assets (guarantees logo & images load in standalone mode)
Route::get('media/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('media.serve');

Route::get('storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.serve');

Route::middleware('guest')->group(function () {
    Route::get('/setup', [\App\Http\Controllers\SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup', [\App\Http\Controllers\SetupController::class, 'store'])->name('setup.store');
});

Route::middleware(['auth', 'verified', 'setup'])->group(function () {

    // Common routes for all active roles (Admin, Pharmacist, Cashier)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [DashboardController::class, 'liveData'])->name('dashboard.live');

    // Customer Returns routes (must be defined BEFORE Route::resource('sales') so 'returns' is not matched as {sale} ID parameter)
    Route::middleware('role:admin,pharmacist')->group(function () {
        Route::get('sales/returns', [\App\Http\Controllers\SaleReturnController::class, 'index'])->name('sales.returns.index');
        Route::get('sales/{sale}/return', [\App\Http\Controllers\SaleReturnController::class, 'create'])->name('sales.returns.create');
        Route::post('sales/returns', [\App\Http\Controllers\SaleReturnController::class, 'store'])->name('sales.returns.store');
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
        Route::get('stock/adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
        Route::get('stock/adjustments/create', [\App\Http\Controllers\StockAdjustmentController::class, 'create'])->name('stock.adjustments.create');
        Route::post('stock/adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');

        // Categories & Suppliers management
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);

        // Expense Management & Expense Categories
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
        Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['create', 'show', 'edit']);

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
        Route::get('reports/ledger', [\App\Http\Controllers\StockLedgerController::class, 'index'])->name('reports.ledger');
    });

    // Parameterized wildcard show routes (placed AFTER create routes to prevent {medicine} matching 'create')
    Route::get('medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');
    Route::get('prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

    // Admin-only routes (User Management, System Settings & Database Backups)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);

        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'edit'])->name('settings.edit');
        Route::get('/settings/index', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::delete('/settings/logo', [\App\Http\Controllers\SettingController::class, 'removeLogo'])->name('settings.logo.remove');

        // Client License Management (Status View & Import/Activate)
        Route::get('/settings/license', [\App\Http\Controllers\LicenseController::class, 'index'])->name('settings.license');
        Route::post('/settings/license', [\App\Http\Controllers\LicenseController::class, 'activate'])->name('settings.license.activate');

        // Database Backup Management
        Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [\App\Http\Controllers\BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{filename}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{filename}/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backups.restore');
        Route::post('/backups/upload-restore', [\App\Http\Controllers\BackupController::class, 'uploadRestore'])->name('backups.upload-restore');
        Route::delete('/backups/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');

        // System Diagnostics
        Route::get('/admin/diagnostics', [\App\Http\Controllers\DiagnosticsController::class, 'index'])->name('admin.diagnostics');

        // Vendor Control Center (PharmCare Vendor Portal)
        Route::prefix('vendor-portal')->name('vendor.')->group(function () {
            Route::get('/', [\App\Http\Controllers\VendorPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/clients', [\App\Http\Controllers\VendorPortalController::class, 'clients'])->name('clients');
            Route::post('/clients', [\App\Http\Controllers\VendorPortalController::class, 'storeClient'])->name('clients.store');
            Route::get('/installations', [\App\Http\Controllers\VendorPortalController::class, 'installations'])->name('installations');
            Route::get('/releases', [\App\Http\Controllers\VendorPortalController::class, 'releases'])->name('releases');
            Route::post('/releases', [\App\Http\Controllers\VendorPortalController::class, 'storeRelease'])->name('releases.store');
            Route::get('/license-generator', [\App\Http\Controllers\LicenseController::class, 'generator'])->name('license.generator');
            Route::post('/license-generator', [\App\Http\Controllers\LicenseController::class, 'generate'])->name('license.generate');
        });
    });
});

// Vendor Management Public API Endpoints (called by client desktop apps)
Route::prefix('api/v1')->group(function () {
    Route::post('/license/activate', [\App\Http\Controllers\Api\PortalApiController::class, 'activate']);
    Route::post('/license/verify', [\App\Http\Controllers\Api\PortalApiController::class, 'verify']);
    Route::get('/releases/latest', [\App\Http\Controllers\Api\PortalApiController::class, 'latestRelease']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
