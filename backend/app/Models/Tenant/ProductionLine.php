<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLine extends TenantModel
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'warehouse_id', 'department_id', 'capacity_per_hour', 'status', 'description'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function machines() { return $this->hasMany(Machine::class); }
    public function productionPlans() { return $this->hasMany(ProductionPlan::class); }
}
