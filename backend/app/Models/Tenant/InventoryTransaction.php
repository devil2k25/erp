<?php

namespace App\Models\Tenant;

class InventoryTransaction extends TenantModel
{
    protected $table = 'inventory_transactions';
    public $timestamps = false;
    protected $fillable = [
        'transaction_number', 'product_id', 'warehouse_id', 'transaction_type',
        'reference_type', 'reference_id', 'quantity', 'unit_id', 'unit_cost',
        'batch_number', 'performed_by', 'transaction_date', 'notes',
    ];
    protected $casts = ['transaction_date' => 'datetime', 'quantity' => 'decimal:4'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function performer() { return $this->belongsTo(User::class, 'performed_by'); }
}
