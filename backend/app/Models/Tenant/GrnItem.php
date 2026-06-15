<?php

namespace App\Models\Tenant;

class GrnItem extends TenantModel
{
    protected $table = 'grn_items';
    protected $fillable = [
        'grn_header_id', 'product_id', 'quantity_ordered', 'quantity_received',
        'unit_id', 'unit_price', 'batch_number', 'expiry_date', 'qc_status', 'qc_notes',
    ];
    protected $casts = ['expiry_date' => 'date', 'quantity_ordered' => 'decimal:4', 'quantity_received' => 'decimal:4'];

    public function grnHeader() { return $this->belongsTo(GrnHeader::class, 'grn_header_id'); }
    public function product() { return $this->belongsTo(Product::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
}
