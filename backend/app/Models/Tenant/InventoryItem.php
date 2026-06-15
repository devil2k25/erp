<?php

namespace App\Models\Tenant;

class InventoryItem extends TenantModel
{
    protected $fillable = [
        'product_id', 'warehouse_id', 'batch_number', 'lot_number',
        'quantity_on_hand', 'quantity_reserved', 'manufactured_date',
        'expiry_date', 'unit_cost', 'location_code', 'barcode',
    ];

    protected $casts = [
        'manufactured_date' => 'date',
        'expiry_date' => 'date',
        'quantity_on_hand' => 'decimal:4',
        'quantity_reserved' => 'decimal:4',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }

    public function getQuantityAvailableAttribute(): float
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }
}
