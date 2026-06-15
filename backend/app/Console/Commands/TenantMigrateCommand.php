<?php

namespace App\Console\Commands;

use App\Models\Master\TenantDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate {organization_id : The organization UUID}';
    protected $description = 'Run migrations for a specific tenant database';

    public function handle(): int
    {
        $orgId = $this->argument('organization_id');

        $tenantDb = TenantDatabase::where('organization_id', $orgId)->firstOrFail();

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

        $this->info("Running migrations for tenant: {$tenantDb->db_name}");

        $this->call('migrate', [
            '--database' => 'tenant',
            '--path'     => config('tenancy.tenant_migrations_path'),
            '--force'    => true,
        ]);

        $this->call('db:seed', [
            '--class'    => 'TenantSeeder',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        $this->info("Tenant {$tenantDb->db_name} provisioned successfully.");

        return Command::SUCCESS;
    }
}
