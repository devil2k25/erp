<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'production_line_id', 'machine_type', 'manufacturer',
        'model_number', 'serial_number', 'purchase_date', 'warranty_expiry',
        'status', 'last_maintenance_at', 'next_maintenance_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_at' => 'datetime',
        'next_maintenance_at' => 'datetime',
    ];

    public function productionLine() { return $this->belongsTo(ProductionLine::class); }
    public function logs() { return $this->hasMany(MachineLog::class); }
    public function maintenanceSchedules() { return $this->hasMany(MaintenanceSchedule::class); }
}
