<?php

namespace App\Http\Controllers;

use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    /**
     * AJAX endpoint: Check for available software updates.
     * Called by the dashboard on page load.
     *
     * GET /api/check-update
     */
    public function check(Request $request): JsonResponse
    {
        $result = UpdateService::check();

        // If user previously dismissed this version, mark as not available
        if ($result['available'] && UpdateService::isDismissed($result['version'])) {
            $result['available'] = false;
            $result['dismissed'] = true;
        }

        return response()->json($result);
    }

    /**
     * AJAX endpoint: Dismiss update notification for a specific version.
     *
     * POST /api/dismiss-update
     */
    public function dismiss(Request $request): JsonResponse
    {
        $request->validate(['version' => 'required|string']);

        UpdateService::dismiss($request->version);

        return response()->json(['success' => true, 'message' => 'Update notification dismissed.']);
    }
}
