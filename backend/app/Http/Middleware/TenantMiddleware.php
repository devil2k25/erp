<?php

namespace App\Http\Middleware;

use App\Models\Master\PersonalAccessToken;
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
        // Primary: resolve tenant from the bearer token's organization_id
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $accessToken = PersonalAccessToken::findToken($bearerToken);
            if ($accessToken?->organization_id) {
                $tenantDb = TenantDatabase::with('organization')
                    ->where('organization_id', $accessToken->organization_id)
                    ->where('is_provisioned', true)
                    ->first();

                if ($tenantDb) {
                    $this->tenantService->connectToTenant($tenantDb);
                    app()->instance('currentTenant', $tenantDb->organization);
                    return $next($request);
                }
            }
        }

        // Fallback: X-Organization-Slug header (supports subdomain too)
        $orgSlug = $request->header(config('tenancy.org_header', 'X-Organization-Slug'))
            ?? $this->extractFromSubdomain($request);

        if ($orgSlug) {
            $tenantDb = TenantDatabase::with('organization')
                ->whereHas('organization', fn($q) => $q->where('slug', $orgSlug)->whereIn('status', ['active', 'trial']))
                ->where('is_provisioned', true)
                ->first();

            if ($tenantDb) {
                $this->tenantService->connectToTenant($tenantDb);
                app()->instance('currentTenant', $tenantDb->organization);
                return $next($request);
            }
        }

        return response()->json(['success' => false, 'error' => 'Organization context required'], 400);
    }

    private function extractFromSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) >= 3 ? $parts[0] : null;
    }
}
