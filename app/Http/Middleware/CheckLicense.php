<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces license validity on every authenticated request.
 *
 * Behaviour by license status:
 *  - ACTIVE:      pass through; no UI changes.
 *  - GRACE:       pass through; share warning banner data with views.
 *  - EXPIRED:     pass through for DEFAULT features; block premium-only routes.
 *  - SUSPENDED/REVOKED: redirect to a license-status page.
 *  - PENDING:     redirect to the activation screen.
 *
 * This middleware does NOT block the activation route, the login flow,
 * the setup wizard, static assets, or diagnostic routes.
 */
class CheckLicense
{
    /**
     * Routes that are exempt from license enforcement.
     */
    protected array $except = [
        'login',
        'logout',
        'register',
        'password.*',
        'setup.*',
        'settings.license',
        'settings.license.*',
        'license.*',
        'media.serve',
        'storage.serve',
        'admin.diagnostics',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip for routes exempt from license checks
        if ($this->isExempt($request)) {
            return $next($request);
        }

        $statusInfo = LicenseService::getStatusInfo();
        $status = $statusInfo['status'];

        // ── PENDING (no license at all) ────────────────────────────────
        // Allow the app to work without a license (DEFAULT edition).
        // Simply share the status so views can show an "Activate" nudge.

        // ── SUSPENDED / REVOKED ────────────────────────────────────────
        if (in_array($status, ['SUSPENDED', 'REVOKED', 'DEACTIVATED'])) {
            // Still allow access to license page and diagnostics
            if ($request->routeIs('settings.license', 'settings.license.*', 'admin.diagnostics')) {
                return $next($request);
            }

            return response()->view('license.suspended', [
                'statusInfo' => $statusInfo,
            ], 403);
        }

        // ── GRACE / EXPIRING SOON ──────────────────────────────────────
        // Share warning data with all views so the layout can show a banner
        if ($statusInfo['show_warning']) {
            view()->share('licenseWarning', $statusInfo['warning_message']);
        }

        // ── EXPIRED (past grace) ───────────────────────────────────────
        // Downgrade to DEFAULT edition features — already handled by
        // LicenseService::getEdition() returning 'DEFAULT'.
        // The feature_enabled() helper will automatically restrict premium features.

        // Share license status info with every view
        view()->share('licenseStatus', $statusInfo);

        // ── Clock rollback detection ───────────────────────────────────
        if (LicenseService::detectClockRollback()) {
            view()->share('licenseWarning', 'System clock anomaly detected. Please verify your system date and time to avoid license issues.');
        }

        return $next($request);
    }

    /**
     * Determine if the request should bypass license checks.
     */
    protected function isExempt(Request $request): bool
    {
        // Non-route requests (e.g. OPTIONS, favicon)
        if (!$request->route()) {
            return true;
        }

        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
