<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PharmCare Application Version
    |--------------------------------------------------------------------------
    |
    | Semantic version reported to the management portal and displayed
    | in the diagnostics screen. Updated with every release.
    |
    */

    'version' => '2.2.0',

    /*
    |--------------------------------------------------------------------------
    | RSA-2048 Public Verification Key (PEM)
    |--------------------------------------------------------------------------
    |
    | Used by the desktop application to verify license signatures.
    | The corresponding private key must NEVER be shipped with the app;
    | it resides only on the vendor's management portal / build machine.
    |
    | At runtime the service will also check storage/keys/public.key
    | and prefer it over this value if present.
    |
    */

    'public_key' => env('LICENSE_PUBLIC_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Grace Period (days)
    |--------------------------------------------------------------------------
    |
    | Number of days after license expiry during which the application
    | will continue to operate normally while showing a warning banner.
    | After this period, premium features are restricted.
    |
    */

    'grace_days' => (int) env('LICENSE_GRACE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Offline Verification Window (days)
    |--------------------------------------------------------------------------
    |
    | Maximum number of days between online license verification calls.
    | Within this window the cached verification is trusted.
    |
    */

    'offline_window_days' => (int) env('LICENSE_OFFLINE_WINDOW', 30),

    /*
    |--------------------------------------------------------------------------
    | Management Portal URL
    |--------------------------------------------------------------------------
    |
    | Base URL of the PharmCare Management Portal API.
    | Leave null when no portal is deployed yet.
    |
    */

    'portal_url' => env('PHARMCARE_PORTAL_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Portal API Key (Installation Token)
    |--------------------------------------------------------------------------
    |
    | Bearer token used to authenticate this installation with the portal.
    | Generated during activation and stored locally.
    |
    */

    'portal_api_key' => env('PHARMCARE_PORTAL_API_KEY', null),

];
