<?php

namespace App\Services\Auth;

use App\Models\Master\TenantDatabase;
use App\Models\Master\UserOrgMap;
use App\Models\Tenant\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private TenantService $tenantService) {}

    public function login(string $email, string $password): array
    {
        // Resolve which org this user belongs to
        $orgMap = UserOrgMap::where('email', $email)->first();
        if (!$orgMap) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $tenantDb = TenantDatabase::with('organization')
            ->where('organization_id', $orgMap->organization_id)
            ->where('is_provisioned', true)
            ->first();

        if (!$tenantDb) {
            throw ValidationException::withMessages(['email' => ['Organization is not provisioned yet.']]);
        }

        // Connect to the tenant DB before authenticating
        $this->tenantService->connectToTenant($tenantDb);

        $user = User::where('email', $email)->where('is_active', true)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $user->update(['last_login_at' => now()]);

        // Token is stored in master DB (custom PersonalAccessToken model)
        $newToken = $user->createToken('api-token');
        // Tag the token with the org so TenantMiddleware can resolve it without the header
        $newToken->accessToken->update(['organization_id' => $orgMap->organization_id]);

        return [
            'user'         => $user->load('roles'),
            'token'        => $newToken->plainTextToken,
            'organization' => $tenantDb->organization,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
