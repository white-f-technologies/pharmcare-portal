<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortalInstallation;
use App\Models\PortalRelease;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class PortalApiController extends Controller
{
    /**
     * API: Online License Activation
     * POST /api/v1/license/activate
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key'     => 'required|string',
            'installation_id' => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License key not found.',
            ], 440);
        }

        if ($license->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => "License is currently {$license->status}.",
            ], 403);
        }

        // Register installation record
        PortalInstallation::updateOrCreate(
            ['installation_id' => $request->installation_id],
            [
                'license_key'        => $license->license_key,
                'client_id'          => $license->business_id,
                'app_version'        => $request->header('X-App-Version', '2.1.0'),
                'hostname'           => $request->input('hostname'),
                'os_info'            => $request->input('os'),
                'first_activated_at' => now(),
                'last_verified_at'   => now(),
                'status'             => 'ACTIVE',
            ]
        );

        $payload = json_decode($license->raw_payload, true);
        if (!$payload) {
            $payload = [
                'license_id'            => $license->license_key,
                'business_name'         => $license->business_name,
                'business_id'           => $license->business_id,
                'edition'               => $license->edition,
                'license_type'          => $license->license_type,
                'issue_date'            => $license->issue_date?->format('Y-m-d'),
                'expiry_date'           => $license->expiry_date?->format('Y-m-d'),
                'activated_modules'     => $license->activated_modules,
                'installation_identity' => $request->installation_id,
                'signature'             => $license->signature,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'License activated successfully.',
            'license' => $payload,
        ]);
    }

    /**
     * API: License Heartbeat & Verification Check
     * POST /api/v1/license/verify
     */
    public function verify(Request $request)
    {
        $request->validate([
            'installation_id' => 'required|string',
            'license_key'     => 'required|string',
        ]);

        $license = License::where('license_key', $request->license_key)->first();
        if (!$license) {
            return response()->json(['status' => 'INVALID', 'message' => 'License record not found.']);
        }

        // Update installation heartbeat
        PortalInstallation::where('installation_id', $request->installation_id)->update([
            'last_verified_at' => now(),
            'app_version'      => $request->header('X-App-Version', '2.1.0'),
        ]);

        $status = LicenseService::getLicenseStatus($license);

        return response()->json([
            'status'          => $status,
            'edition'         => $license->edition,
            'expiry_date'     => $license->expiry_date?->format('Y-m-d'),
            'days_remaining'  => LicenseService::daysRemaining($license),
            'verified_at'     => now()->toIso8601String(),
        ]);
    }

    /**
     * API: Check Latest Release
     * GET /api/v1/releases/latest
     */
    public function latestRelease(Request $request)
    {
        $latest = PortalRelease::where('status', 'PUBLISHED')
            ->latest('release_date')
            ->first();

        if (!$latest) {
            return response()->json([
                'version'      => config('license.version', '2.1.0'),
                'release_date' => date('Y-m-d'),
                'download_url' => '',
                'notes'        => 'Up to date.',
            ]);
        }

        return response()->json([
            'version'               => $latest->version,
            'release_date'          => $latest->release_date->format('Y-m-d'),
            'download_url'          => $latest->download_url,
            'release_notes'         => $latest->release_notes,
            'min_supported_version' => $latest->min_supported_version,
            'requires_db_migration' => $latest->requires_db_migration,
        ]);
    }
}
