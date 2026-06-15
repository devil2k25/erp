<?php

namespace App\Services\Auth;

use App\Models\Master\Organization;
use App\Models\Master\Plan;
use App\Models\Master\Subscription;
use App\Services\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationOnboardingService
{
    public function __construct(private TenantService $tenantService) {}

    public function register(array $data): array
    {
        $plan = Plan::where('slug', $data['plan_slug'] ?? 'basic')->firstOrFail();

        $org = DB::connection('master')->transaction(function () use ($data, $plan) {
            $org = Organization::create([
                'name'          => $data['organization_name'],
                'slug'          => Str::slug($data['organization_name']) . '-' . Str::random(4),
                'owner_name'    => $data['owner_name'],
                'owner_email'   => $data['owner_email'],
                'owner_phone'   => $data['owner_phone'] ?? null,
                'industry_type' => $data['industry_type'] ?? null,
                'timezone'      => $data['timezone'] ?? 'UTC',
                'status'        => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            Subscription::create([
                'organization_id' => $org->id,
                'plan_id'         => $plan->id,
                'status'          => 'trialing',
                'starts_at'       => now(),
                'ends_at'         => now()->addDays(14),
                'billing_cycle'   => 'monthly',
                'amount_paid'     => 0,
            ]);

            return $org;
        });

        $tenantDb = $this->tenantService->provisionTenantDatabase($org);

        $this->tenantService->connectToTenant($tenantDb);

        $user = \App\Models\Tenant\User::create([
            'first_name' => $data['owner_name'],
            'last_name'  => '',
            'email'      => $data['owner_email'],
            'password'   => Hash::make($data['password']),
            'is_active'  => true,
        ]);

        $user->assignRole('factory_owner');

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'organization' => $org,
            'user'         => $user,
            'token'        => $token,
        ];
    }
}
