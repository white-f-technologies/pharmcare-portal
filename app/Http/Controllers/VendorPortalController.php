<?php

namespace App\Http\Controllers;

use App\Models\PortalClient;
use App\Models\PortalInstallation;
use App\Models\PortalRelease;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class VendorPortalController extends Controller
{
    /**
     * Dashboard of the PharmCare Vendor Control Center.
     */
    public function dashboard()
    {
        $totalClients = PortalClient::count();
        $totalInstallations = PortalInstallation::count();
        $totalLicenses = License::count();
        $activeLicenses = License::where('status', 'ACTIVE')->count();
        $premiumClients = License::where('edition', 'PREMIUM')->count();
        $defaultClients = License::where('edition', 'DEFAULT')->count();
        $latestRelease = PortalRelease::where('status', 'PUBLISHED')->latest('release_date')->first();

        $recentClients = PortalClient::latest()->take(5)->get();
        $recentInstallations = PortalInstallation::latest()->take(5)->get();
        $recentLicenses = License::latest()->take(5)->get();

        return view('vendor_portal.dashboard', compact(
            'totalClients',
            'totalInstallations',
            'totalLicenses',
            'activeLicenses',
            'premiumClients',
            'defaultClients',
            'latestRelease',
            'recentClients',
            'recentInstallations',
            'recentLicenses'
        ));
    }

    /**
     * Client Management List & Create.
     */
    public function clients()
    {
        $clients = PortalClient::latest()->paginate(15);
        return view('vendor_portal.clients', compact('clients'));
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_name' => 'required|string|max:255',
            'owner_name'    => 'required|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
        ]);

        $validated['client_id'] = PortalClient::generateClientId();
        $validated['status'] = 'ACTIVE';

        PortalClient::create($validated);

        return back()->with('success', 'Client ' . $validated['pharmacy_name'] . ' (' . $validated['client_id'] . ') added successfully!');
    }

    /**
     * Releases & Update Management.
     */
    public function releases()
    {
        $releases = PortalRelease::latest('release_date')->get();
        return view('vendor_portal.releases', compact('releases'));
    }

    public function storeRelease(Request $request)
    {
        $validated = $request->validate([
            'version'               => 'required|string|max:20|unique:portal_releases,version',
            'release_date'          => 'required|date',
            'download_url'          => 'required|url',
            'release_notes'         => 'nullable|string',
            'min_supported_version' => 'nullable|string',
            'requires_db_migration' => 'nullable|boolean',
        ]);

        $validated['requires_db_migration'] = $request->boolean('requires_db_migration');
        $validated['status'] = 'PUBLISHED';

        PortalRelease::create($validated);

        return back()->with('success', 'Release v' . $validated['version'] . ' published successfully!');
    }

    /**
     * Track Client Installations.
     */
    public function installations()
    {
        $installations = PortalInstallation::latest('updated_at')->paginate(20);
        return view('vendor_portal.installations', compact('installations'));
    }
}
