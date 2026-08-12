<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return $this->edit();
    }

    public function edit()
    {
        $settingsMap = Setting::pluck('value', 'key')->toArray();
        $settings = [
            'app_name' => setting('app_name', setting('system_name', config('app.name', 'PharmCare'))),
            'app_logo' => setting('app_logo', setting('system_logo')),
            'currency_symbol' => setting('currency_symbol', setting('currency', '$')),
            'system_email' => setting('system_email', setting('contact_email', '')),
            'system_phone' => setting('system_phone', setting('contact_phone', '')),
            'system_address' => setting('system_address', setting('address', '')),
        ];

        return view('settings.edit', compact('settings', 'settingsMap'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'system_name' => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'system_email' => 'nullable|email|max:255',
            'contact_email' => 'nullable|email|max:255',
            'system_phone' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:50',
            'system_address' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:100',
            'receipt_footer' => 'nullable|string|max:500',
            'app_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'system_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $appName = $request->input('app_name', $request->input('system_name', 'PharmCare'));
        $currency = $request->input('currency_symbol', $request->input('currency', '$'));
        $email = $request->input('system_email', $request->input('contact_email', ''));
        $phone = $request->input('system_phone', $request->input('contact_phone', ''));
        $address = $request->input('system_address', $request->input('address', ''));
        $taxNumber = $request->input('tax_number', '');
        $receiptFooter = $request->input('receipt_footer', '');

        Setting::set('app_name', $appName);
        Setting::set('system_name', $appName);
        Setting::set('currency_symbol', $currency);
        Setting::set('currency', $currency);
        Setting::set('system_email', $email);
        Setting::set('contact_email', $email);
        Setting::set('system_phone', $phone);
        Setting::set('contact_phone', $phone);
        Setting::set('system_address', $address);
        Setting::set('address', $address);
        Setting::set('tax_number', $taxNumber);
        Setting::set('receipt_footer', $receiptFooter);

        $file = $request->file('app_logo') ?? $request->file('system_logo');
        if ($file) {
            $oldLogo = setting('app_logo') ?? setting('system_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $logoPath = $file->store('settings', 'public');
            Setting::set('app_logo', $logoPath);
            Setting::set('system_logo', $logoPath);
        }

        $this->logActivity('settings_updated', 'Setting', null, 'System settings updated.');

        $redirectRoute = routeIsDefined('settings.edit') ? 'settings.edit' : 'settings.index';
        return redirect()->route($redirectRoute)->with('success', 'System settings updated successfully.');
    }

    public function removeLogo()
    {
        $oldLogo = setting('app_logo') ?? setting('system_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        Setting::set('app_logo', null);
        Setting::set('system_logo', null);

        $this->logActivity('settings_logo_removed', 'Setting', null, 'System logo removed.');

        $redirectRoute = routeIsDefined('settings.edit') ? 'settings.edit' : 'settings.index';
        return redirect()->route($redirectRoute)->with('success', 'System logo removed successfully.');
    }
}

if (!function_exists('routeIsDefined')) {
    function routeIsDefined($name) {
        return \Illuminate\Support\Facades\Route::has($name);
    }
}
