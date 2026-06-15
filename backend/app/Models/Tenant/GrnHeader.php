<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class GrnHeader extends TenantModel
{
    use SoftDeletes;

    protected $table = 'grn_headers';
    protected $fillable = [
        'grn_number', 'vendor_id', 'purchase_order_ref', 'received_by',
        'warehouse_id', 'received_date', 'status', 'notes', 'total_amount',
    ];
    protected $casts = ['received_date' => 'date'];

    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
    public function items() { return $this->hasMany(GrnItem::class, 'grn_header_id'); }
}
