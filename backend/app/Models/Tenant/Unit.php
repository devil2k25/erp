<?php

namespace App\Models\Tenant;

class Unit extends TenantModel
{
    protected $fillable = ['name', 'symbol', 'type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
