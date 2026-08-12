<?php

namespace App\Http\Middleware;

use App\Services\SetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $setup = app(SetupService::class);

        if (!$setup->isInstalled()) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
