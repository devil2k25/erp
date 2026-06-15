<?php

namespace App\Models\Tenant;

class QcEntry extends TenantModel
{
    protected $table = 'qc_entries';
    protected $fillable = [
        'reference_type', 'reference_id', 'product_id', 'inspected_quantity',
        'passed_quantity', 'failed_quantity', 'defect_type', 'inspector_id',
        'inspection_date', 'status', 'notes',
    ];
    protected $casts = ['inspection_date' => 'date'];

    public function product() { return $this->belongsTo(Product::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }
}
