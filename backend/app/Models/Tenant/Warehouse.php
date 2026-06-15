<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends TenantModel
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'type', 'address', 'capacity', 'capacity_unit_id', 'manager_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function capacityUnit() { return $this->belongsTo(Unit::class, 'capacity_unit_id'); }
    public function inventoryItems() { return $this->hasMany(InventoryItem::class); }
}
