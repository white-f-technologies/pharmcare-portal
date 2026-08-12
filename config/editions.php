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
| To add a new premium feature in the future, simply add its key
| to the 'PREMIUM' array — no code changes elsewhere.
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
    | Core pharmacy features available to every licensed installation.
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
    | Everything in DEFAULT plus advanced modules.
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

        // --- premium-only ---
        'advanced_reports',
        'advanced_inventory',
        'ledger_audit',
        'prescription',
        'fefo',
        'stock_ageing',
        'approval_workflows',
        'multi_terminal',
        'advanced_analytics',
        'stock_ledger',
        'medicine_images',
    ],

];
