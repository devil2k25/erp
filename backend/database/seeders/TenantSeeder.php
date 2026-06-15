<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'inventory'  => ['view', 'create', 'update', 'delete', 'approve'],
            'production' => ['view', 'create', 'update', 'delete', 'approve'],
            'workers'    => ['view', 'create', 'update', 'delete'],
            'machines'   => ['view', 'create', 'update', 'delete'],
            'maintenance'=> ['view', 'create', 'update', 'delete'],
            'qc'         => ['view', 'create', 'update', 'delete'],
            'reports'    => ['view', 'export'],
            'master_data'=> ['view', 'create', 'update', 'delete'],
            'settings'   => ['view', 'update'],
            'dashboard'  => ['view'],
        ];

        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $perm = Permission::updateOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'api'],
                    ['display_name' => ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $module)), 'module' => $module]
                );
                $allPermissions[] = $perm->name;
            }
        }

        $roles = [
            'super_admin'        => $allPermissions,
            'factory_owner'      => $allPermissions,
            'production_manager' => ['production.view','production.create','production.update','production.approve','workers.view','machines.view','dashboard.view','reports.view','reports.export','master_data.view'],
            'inventory_manager'  => ['inventory.view','inventory.create','inventory.update','inventory.approve','master_data.view','dashboard.view','reports.view','reports.export'],
            'hr_manager'         => ['workers.view','workers.create','workers.update','dashboard.view','reports.view'],
            'qc_manager'         => ['qc.view','qc.create','qc.update','production.view','inventory.view','dashboard.view','reports.view'],
            'worker'             => ['production.view','production.create','dashboard.view'],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'api'],
                ['display_name' => ucwords(str_replace('_', ' ', $roleName))]
            );
            $role->syncPermissions($permissions);
        }
    }
}
