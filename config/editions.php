<?php

/*
|--------------------------------------------------------------------------
| Edition Feature Entitlements
|--------------------------------------------------------------------------
|
| Maps each edition to the set of feature modules it unlocks.
| The LicenseService checks this map when application code calls
| feature_enabled('some_feature').
|
| IMPORTANT: Only list features that have REAL working code behind them.
| Do NOT add placeholder feature names with no controllers/views.
|
| Per-license overrides are possible via the `activated_modules` JSON
| field stored in the license payload; those are merged on top of
| the edition defaults.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | DEFAULT Edition
    |----------------------------------------------------------------------
    | Core pharmacy features available to every installation.
    | This covers 100% of daily pharmacy operations.
    */

    'DEFAULT' => [
        'pos',
        'medicines',
        'unit_packaging',
        'inventory',
        'batches',
        'suppliers',
        'customers',
        'sales',
        'purchases',
        'expenses',
        'reports',
        'backup',
        'settings',
        'prescriptions',
        'medicine_images',
    ],

    /*
    |----------------------------------------------------------------------
    | PREMIUM Edition
    |----------------------------------------------------------------------
    | Everything in DEFAULT plus advanced reporting & audit modules.
    | Each premium feature has real working code gating it.
    */

    'PREMIUM' => [
        // --- inherited from DEFAULT ---
        'pos',
        'medicines',
        'unit_packaging',
        'inventory',
        'batches',
        'suppliers',
        'customers',
        'sales',
        'purchases',
        'expenses',
        'reports',
        'backup',
        'settings',
        'prescriptions',
        'medicine_images',

        // --- premium-only (real working code) ---
        'advanced_reports',     // CSV export on Sales Report
        'advanced_inventory',   // CSV export on Inventory Report + cost/retail columns
        'stock_ledger',         // Full Stock Ledger Audit trail page
    ],

];

