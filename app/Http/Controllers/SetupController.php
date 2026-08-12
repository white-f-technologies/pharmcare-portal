<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\SetupService;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SetupController extends Controller
{
    public function index(SetupService $setup)
    {
        if ($setup->isInstalled()) {
            return redirect()->route('login');
        }

        $activeLicense = LicenseService::getActiveLicense();

        if (!$activeLicense) {
            $detectedJson = $this->autoDetectLicenseFile();
            if ($detectedJson) {
                $actRes = LicenseService::activateLicense($detectedJson);
                if ($actRes['success']) {
                    $activeLicense = $actRes['license'];
                }
            }
        }

        return view('setup.index', compact('activeLicense'));
    }

    protected function autoDetectLicenseFile(): ?string
    {
        $candidatePaths = [
            base_path('pharmcare_license.json'),
            base_path('license.json'),
        ];

        $globRoot = glob(base_path('pharmcare_license*.json'));
        if ($globRoot) {
            $candidatePaths = array_merge($candidatePaths, $globRoot);
        }

        foreach (range('D', 'Z') as $drive) {
            $candidatePaths[] = "{$drive}:\\pharmcare_license.json";
            $candidatePaths[] = "{$drive}:\\license.json";
            $usbGlob = glob("{$drive}:\\pharmcare_license*.json");
            if ($usbGlob) {
                $candidatePaths = array_merge($candidatePaths, $usbGlob);
            }
        }

        foreach (array_unique($candidatePaths) as $path) {
            if (file_exists($path) && is_file($path)) {
                $content = @file_get_contents($path);
                if ($content && json_decode($content, true)) {
                    return $content;
                }
            }
        }

        return null;
    }

    public function store(Request $request, SetupService $setup)
    {
        if ($setup->isInstalled()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'license_file' => 'nullable|file|mimes:json,txt|max:1024',
            'license_json' => 'nullable|string',
            'business_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Process license payload if provided during setup
        $licenseContent = null;
        if ($request->hasFile('license_file')) {
            $licenseContent = file_get_contents($request->file('license_file')->getRealPath());
        } elseif ($request->filled('license_json')) {
            $licenseContent = $request->input('license_json');
        }

        if ($licenseContent) {
            $actRes = LicenseService::activateLicense($licenseContent);
            if (!$actRes['success']) {
                return redirect()->back()
                    ->withErrors(['license_json' => 'License error: ' . $actRes['error']])
                    ->withInput();
            }
        }

        // Configure pharmacy business settings
        Setting::set('app_name', $request->business_name);
        Setting::set('system_name', $request->business_name);
        Setting::set('currency_symbol', $request->currency_symbol);
        Setting::set('currency', $request->currency_symbol);
        Setting::set('system_email', $request->business_email ?? '');
        Setting::set('contact_email', $request->business_email ?? '');
        Setting::set('system_phone', $request->business_phone ?? '');
        Setting::set('contact_phone', $request->business_phone ?? '');
        Setting::set('system_address', $request->business_address ?? '');
        Setting::set('address', $request->business_address ?? '');

        // Create or update real Administrator account
        $bootstrapAdmin = User::where('email', 'admin@pharmcare.local')->first();
        if ($bootstrapAdmin) {
            $bootstrapAdmin->update([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
            ]);
        } else {
            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'phone' => '',
                'is_active' => true,
            ]);
        }

        // Complete setup wizard
        $setup->completeSetup();

        $adminUser = User::where('email', $request->admin_email)->first();
        auth()->login($adminUser);

        return redirect()->route('dashboard')->with('success', 'PharmCare setup complete! Welcome to your pharmacy management system.');
    }
}
