<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends TenantModel
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'manager_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function users() { return $this->hasMany(User::class); }
}
