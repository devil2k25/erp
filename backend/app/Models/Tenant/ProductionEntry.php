<?php

namespace App\Models\Tenant;

class ProductionEntry extends TenantModel
{
    protected $table = 'production_entries';
    protected $fillable = [
        'production_plan_id', 'shift_id', 'machine_id', 'operator_id', 'entry_date',
        'start_time', 'end_time', 'planned_quantity', 'produced_quantity', 'rejected_quantity',
        'rework_quantity', 'downtime_minutes', 'downtime_reason',
        'oee_availability', 'oee_performance', 'oee_quality', 'oee_score', 'notes',
    ];
    protected $casts = [
        'entry_date' => 'date', 'start_time' => 'datetime', 'end_time' => 'datetime',
        'produced_quantity' => 'decimal:4', 'rejected_quantity' => 'decimal:4',
    ];

    public function plan() { return $this->belongsTo(ProductionPlan::class, 'production_plan_id'); }
    public function shift() { return $this->belongsTo(Shift::class); }
    public function machine() { return $this->belongsTo(Machine::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
