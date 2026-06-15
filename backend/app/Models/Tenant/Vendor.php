<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'contact_person', 'email', 'phone',
        'address', 'gst_number', 'payment_terms_days', 'rating', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];
}
