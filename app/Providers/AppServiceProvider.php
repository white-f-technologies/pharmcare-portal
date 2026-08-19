<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('FORCE_HTTPS', false) || (!app()->runningInConsole() && app()->bound('request') && request()->header('X-Forwarded-Proto') === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Ensure Installation ID exists on every boot
        try {
            \App\Services\InstallationService::ensureId();
        } catch (\Throwable $e) {
            // Silently continue — ID will be generated on next successful boot
        }

        // Register @feature / @endfeature Blade directives
        \Illuminate\Support\Facades\Blade::if('feature', function (string $feature) {
            return \App\Services\LicenseService::isModuleEnabled($feature);
        });

        // Register @premium / @endpremium shortcut
        \Illuminate\Support\Facades\Blade::if('premium', function () {
            return \App\Services\LicenseService::isPremium();
        });

        try {
            $settingsMap = [];
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settingsMap = \App\Models\Setting::pluck('value', 'key')->toArray();
            }
            
            $systemName = $settingsMap['system_name'] ?? $settingsMap['app_name'] ?? 'PharmCare';
            $systemLogo = $settingsMap['system_logo'] ?? $settingsMap['app_logo'] ?? null;
            $currency = $settingsMap['currency'] ?? $settingsMap['currency_symbol'] ?? 'UGX';
            $phone = $settingsMap['contact_phone'] ?? $settingsMap['system_phone'] ?? '';
            $email = $settingsMap['contact_email'] ?? $settingsMap['system_email'] ?? '';
            $address = $settingsMap['address'] ?? $settingsMap['system_address'] ?? '';

            $systemSettings = array_merge([
                'system_name' => $systemName,
                'app_name' => $systemName,
                'system_logo' => $systemLogo,
                'app_logo' => $systemLogo,
                'currency' => $currency,
                'currency_symbol' => $currency,
                'contact_phone' => $phone,
                'system_phone' => $phone,
                'contact_email' => $email,
                'system_email' => $email,
                'address' => $address,
                'system_address' => $address,
            ], $settingsMap);

            if (!empty($systemSettings['system_name'])) {
                config(['app.name' => $systemSettings['system_name']]);
            }

            \Illuminate\Support\Facades\View::share('systemSettings', $systemSettings);

            // Share app version and edition with all views
            \Illuminate\Support\Facades\View::share('appVersion', config('license.version', '2.1.0'));
            \Illuminate\Support\Facades\View::share('appEdition', \App\Services\LicenseService::getEdition());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\View::share('systemSettings', [
                'system_name' => 'PharmCare',
                'system_logo' => null,
                'currency' => 'UGX',
                'contact_phone' => '',
                'contact_email' => '',
                'address' => '',
            ]);
            \Illuminate\Support\Facades\View::share('appVersion', config('license.version', '2.1.0'));
            \Illuminate\Support\Facades\View::share('appEdition', 'DEFAULT');
        }
    }
}
