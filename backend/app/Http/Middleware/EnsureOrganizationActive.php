<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return response()->json(['success' => false, 'error' => 'No active organization context'], 401);
        }

        if (in_array($tenant->status, ['suspended', 'cancelled'])) {
            return response()->json(['success' => false, 'error' => 'Organization subscription is ' . $tenant->status], 403);
        }

        return $next($request);
    }
}
