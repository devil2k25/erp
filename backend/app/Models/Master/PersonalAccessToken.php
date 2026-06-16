<?php

namespace App\Models\Master;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    protected $connection = 'master';

    protected $fillable = [
        'tokenable_type', 'tokenable_id', 'name', 'token',
        'abilities', 'expires_at', 'organization_id',
    ];
}
