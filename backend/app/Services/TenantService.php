<?php

namespace App\Services;

use App\Models\Master\Organization;
use App\Models\Master\Plan;
use App\Models\Master\TenantDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function connectToTenant(TenantDatabase $tenantDb): void
    {
        config(['database.connections.tenant' => [
            'driver'    => 'mysql',
            'host'      => $tenantDb->db_host,
            'port'      => $tenantDb->db_port,
            'database'  => $tenantDb->db_name,
            'username'  => $tenantDb->db_username,
            'password'  => decrypt($tenantDb->db_password),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    public function provisionTenantDatabase(Organization $org): TenantDatabase
    {
        $dbName = config('tenancy.db_prefix') . $org->slug;
        $dbUser = 'tenant_' . substr(str_replace('-', '', $org->id), 0, 12);
        $dbPass = Str::random(32);

        $masterPdo = DB::connection('master')->getPdo();
        $masterPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $masterPdo->exec("CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPass}'");
        $masterPdo->exec("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'");
        $masterPdo->exec("FLUSH PRIVILEGES");

        $tenantDb = TenantDatabase::create([
            'organization_id' => $org->id,
            'db_name'         => $dbName,
            'db_host'         => config('database.connections.master.host', '127.0.0.1'),
            'db_port'         => config('database.connections.master.port', 3306),
            'db_username'     => $dbUser,
            'db_password'     => encrypt($dbPass),
            'is_provisioned'  => false,
        ]);

        Artisan::call('tenant:migrate', ['organization_id' => $org->id]);

        $tenantDb->update(['is_provisioned' => true, 'provisioned_at' => now()]);

        return $tenantDb->fresh();
    }

    public function disconnectTenant(): void
    {
        DB::purge('tenant');
        DB::setDefaultConnection('master');
    }
}
