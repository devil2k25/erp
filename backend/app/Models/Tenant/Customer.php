<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'contact_person', 'email', 'phone',
        'address', 'gst_number', 'credit_limit', 'payment_terms_days', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];
}
