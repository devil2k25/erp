<?php

namespace App\Models\Tenant;

class MaintenanceSchedule extends TenantModel
{
    protected $table = 'maintenance_schedules';
    protected $fillable = [
        'machine_id', 'title', 'maintenance_type', 'scheduled_date', 'completed_date',
        'technician_id', 'status', 'estimated_duration_hours', 'actual_duration_hours',
        'cost', 'notes',
    ];
    protected $casts = ['scheduled_date' => 'date', 'completed_date' => 'date'];

    public function machine() { return $this->belongsTo(Machine::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
}
