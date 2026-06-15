<?php

namespace App\Models\Tenant;

class BomItem extends TenantModel
{
    protected $table = 'bom_items';
    protected $fillable = ['parent_product_id', 'component_product_id', 'quantity', 'unit_id', 'scrap_percentage', 'sequence', 'notes', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'quantity' => 'decimal:4', 'scrap_percentage' => 'decimal:2'];

    public function parentProduct() { return $this->belongsTo(Product::class, 'parent_product_id'); }
    public function component() { return $this->belongsTo(Product::class, 'component_product_id'); }
    public function unit() { return $this->belongsTo(Unit::class); }

    public function getRequiredQuantityAttribute(): float
    {
        return $this->quantity * (1 + $this->scrap_percentage / 100);
    }
}
