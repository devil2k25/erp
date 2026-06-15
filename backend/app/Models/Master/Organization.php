<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasUuids, SoftDeletes;

    protected $connection = 'master';
    protected $table = 'organizations';

    protected $fillable = [
        'name', 'slug', 'owner_name', 'owner_email', 'owner_phone',
        'logo_path', 'industry_type', 'address', 'country', 'timezone',
        'status', 'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function tenantDatabase(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TenantDatabase::class);
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }
}
