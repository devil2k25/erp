<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionPlan extends TenantModel
{
    use SoftDeletes;

    protected $table = 'production_plans';
    protected $fillable = [
        'plan_number', 'product_id', 'production_line_id', 'planned_quantity', 'actual_quantity',
        'planned_start_date', 'planned_end_date', 'actual_start_date', 'actual_end_date',
        'status', 'priority', 'batch_number', 'notes', 'created_by', 'approved_by',
    ];
    protected $casts = [
        'planned_start_date' => 'datetime', 'planned_end_date' => 'datetime',
        'actual_start_date' => 'datetime', 'actual_end_date' => 'datetime',
        'planned_quantity' => 'decimal:4', 'actual_quantity' => 'decimal:4',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function productionLine() { return $this->belongsTo(ProductionLine::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function entries() { return $this->hasMany(ProductionEntry::class); }
}
