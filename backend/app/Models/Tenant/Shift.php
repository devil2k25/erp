<?php

namespace App\Models\Tenant;

class Shift extends TenantModel
{
    protected $fillable = ['name', 'code', 'start_time', 'end_time', 'break_duration_mins', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
