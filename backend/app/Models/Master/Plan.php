<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuids;

    protected $connection = 'master';
    protected $table = 'plans';

    protected $fillable = [
        'name', 'slug', 'max_users', 'max_products', 'max_warehouses',
        'price_monthly', 'price_yearly', 'features', 'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];
}
