<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'unit_id', 'type',
        'description', 'hsn_code', 'tax_rate', 'cost_price', 'selling_price',
        'min_stock_level', 'max_stock_level', 'reorder_point', 'lead_time_days',
        'image_path', 'is_trackable', 'is_active',
    ];

    protected $casts = [
        'is_trackable' => 'boolean',
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function bomItems() { return $this->hasMany(BomItem::class, 'parent_product_id'); }
    public function inventoryItems() { return $this->hasMany(InventoryItem::class); }

    public function getTotalStockAttribute(): float
    {
        return $this->inventoryItems()->sum('quantity_on_hand');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->reorder_point;
    }
}
