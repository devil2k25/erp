<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantDatabase extends Model
{
    use HasUuids;

    protected $connection = 'master';
    protected $table = 'tenant_databases';

    protected $fillable = [
        'organization_id', 'db_name', 'db_host', 'db_port',
        'db_username', 'db_password', 'is_provisioned', 'provisioned_at',
    ];

    protected $hidden = ['db_password'];

    protected $casts = [
        'is_provisioned' => 'boolean',
        'provisioned_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
