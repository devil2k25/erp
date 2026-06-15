<?php

namespace App\Http\Middleware;

use App\Models\Master\TenantDatabase;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(private TenantService $tenantService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $orgSlug = $request->header(config('tenancy.org_header', 'X-Organization-Slug'))
            ?? $this->extractFromSubdomain($request);

        if (!$orgSlug) {
            return response()->json(['success' => false, 'error' => 'Organization identifier required'], 400);
        }

        $tenantDb = TenantDatabase::with('organization')
            ->whereHas('organization', fn($q) => $q->where('slug', $orgSlug)->whereIn('status', ['active', 'trial']))
            ->first();

        if (!$tenantDb || !$tenantDb->is_provisioned) {
            return response()->json(['success' => false, 'error' => 'Organization not found or not provisioned'], 404);
        }

        $this->tenantService->connectToTenant($tenantDb);
        app()->instance('currentTenant', $tenantDb->organization);

        return $next($request);
    }

    private function extractFromSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) >= 3 ? $parts[0] : null;
    }
}
