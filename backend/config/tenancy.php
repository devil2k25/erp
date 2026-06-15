<?php

return [
    'master_connection' => 'master',
    'tenant_connection' => 'tenant',
    'org_header' => 'X-Organization-Slug',
    'db_prefix' => 'erp_tenant_',
    'tenant_migrations_path' => database_path('migrations/tenant'),
];
