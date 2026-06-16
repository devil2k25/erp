<?php

namespace Database\Seeders;

use App\Models\Master\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::firstOrCreate(
            ['email' => 'admin@erp.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('admin@123'),
                'is_active' => true,
            ]
        );
    }
}
