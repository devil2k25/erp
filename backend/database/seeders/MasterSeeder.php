<?php

namespace Database\Seeders;

use App\Models\Master\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'           => 'Basic',
                'slug'           => 'basic',
                'max_users'      => 10,
                'max_products'   => 500,
                'max_warehouses' => 3,
                'price_monthly'  => 49.00,
                'price_yearly'   => 490.00,
                'features'       => ['inventory', 'production', 'attendance', 'basic_reports'],
            ],
            [
                'name'           => 'Professional',
                'slug'           => 'professional',
                'max_users'      => 50,
                'max_products'   => 5000,
                'max_warehouses' => 10,
                'price_monthly'  => 149.00,
                'price_yearly'   => 1490.00,
                'features'       => ['inventory', 'production', 'attendance', 'qc', 'maintenance', 'reports', 'realtime'],
            ],
            [
                'name'           => 'Enterprise',
                'slug'           => 'enterprise',
                'max_users'      => 999,
                'max_products'   => 99999,
                'max_warehouses' => 999,
                'price_monthly'  => 499.00,
                'price_yearly'   => 4990.00,
                'features'       => ['inventory', 'production', 'attendance', 'qc', 'maintenance', 'reports', 'realtime', 'ai_insights', 'api_access', 'sso'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
