<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use App\Services\InstallationService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display the license status and activation form.
     */
    public function index()
    {
        $activeLicense = LicenseService::getActiveLicense();
        $edition = LicenseService::getEdition();
        $isPremium = LicenseService::isPremium();
        $statusInfo = LicenseService::getStatusInfo();
        $installationId = InstallationService::getId();
        $lastVerification = LicenseService::getLastVerification();

        return view('settings.license', compact(
            'activeLicense',
            'edition',
            'isPremium',
            'statusInfo',
            'installationId',
            'lastVerification'
        ));
    }

    /**
     * Process license activation (file upload or JSON paste).
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_file' => 'nullable|file|mimes:json,txt|max:1024',
            'license_json' => 'nullable|string',
        ]);

        $content = null;

        if ($request->hasFile('license_file')) {
            $content = file_get_contents($request->file('license_file')->getRealPath());
        } elseif ($request->filled('license_json')) {
            $content = $request->input('license_json');
        } else {
            return back()->with('error', 'Please provide a license JSON string or upload a license file.');
        }

        $result = LicenseService::activateLicense($content);

        if ($result['success']) {
            return redirect()->route('settings.license')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['error']);
    }

    /**
     * Display the Offline License Key Generator UI.
     */
    public function generator(Request $request)
    {
        $publicKeyPem = LicenseService::getPublicKeyPem();
        $hasPrivateKey = LicenseService::hasPrivateKey();
        $installationId = InstallationService::getId();

        $clientRef = $request->query('client');
        $prefilledClient = null;
        if ($clientRef) {
            $prefilledClient = \App\Models\PortalClient::where('client_id', $clientRef)->first();
        }

        return view('settings.license_generator', compact(
            'publicKeyPem',
            'hasPrivateKey',
            'installationId',
            'prefilledClient'
        ));
    }

    /**
     * Generate signed license key JSON payload.
     */
    public function generate(Request $request)
    {
        if (!LicenseService::hasPrivateKey()) {
            return back()->with('error', 'Unauthorized: License key generation requires the Vendor Private Key (keys/private.key), which is excluded from buyer client installations.');
        }

        $validated = $request->validate([
            'business_name'  => 'required|string|max:255',
            'business_id'    => 'nullable|string|max:100',
            'edition'        => 'required|in:PREMIUM,DEFAULT',
            'license_type'   => 'required|in:PERPETUAL,SUBSCRIPTION',
            'expiry_days'    => 'nullable|integer|min:1',
            'expiry_date'    => 'nullable|date',
            'max_terminals'  => 'nullable|integer|min:1|max:100',
            'grace_days'     => 'nullable|integer|min:0|max:90',
            'action'         => 'required|in:download,activate_now,json_view',
        ]);

        $expiryDate = null;
        if ($validated['license_type'] === 'SUBSCRIPTION') {
            if (!empty($validated['expiry_days'])) {
                $expiryDate = date('Y-m-d', strtotime('+' . $validated['expiry_days'] . ' days'));
            } elseif (!empty($validated['expiry_date'])) {
                $expiryDate = $validated['expiry_date'];
            } else {
                $expiryDate = date('Y-m-d', strtotime('+365 days')); // default 1 year
            }
        }

        try {
            $payload = LicenseService::generateLicensePayload(
                businessName: $validated['business_name'],
                businessId: $validated['business_id'],
                edition: $validated['edition'],
                licenseType: $validated['license_type'],
                expiryDate: $expiryDate,
                maxTerminals: (int) ($validated['max_terminals'] ?? 1),
                graceDays: isset($validated['grace_days']) ? (int) $validated['grace_days'] : null,
            );

            $jsonString = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($validated['action'] === 'download') {
                $filename = 'pharmcare_license_' . strtolower($validated['edition']) . '_' . date('Ymd') . '.json';
                return response()->streamDownload(function () use ($jsonString) {
                    echo $jsonString;
                }, $filename, ['Content-Type' => 'application/json']);
            }

            if ($validated['action'] === 'activate_now') {
                $actRes = LicenseService::activateLicense($payload);
                if ($actRes['success']) {
                    return redirect()->route('settings.license')
                        ->with('success', 'License generated and activated immediately! Active Edition: ' . $validated['edition']);
                }
                return back()->with('error', 'Generated key error: ' . $actRes['error']);
            }

            return back()->with([
                'generated_payload' => $jsonString,
                'success' => 'RSA-SHA256 Cryptographic License generated successfully!'
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to generate license: ' . $e->getMessage());
        }
    }
}
