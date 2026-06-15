<?php

namespace App\Models\Tenant;

class MachineLog extends TenantModel
{
    protected $table = 'machine_logs';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'machine_id', 'log_type', 'status_from', 'status_to', 'message',
        'logged_by', 'logged_at', 'production_entry_id',
    ];
    protected $casts = ['logged_at' => 'datetime'];

    public function machine() { return $this->belongsTo(Machine::class); }
    public function logger() { return $this->belongsTo(User::class, 'logged_by'); }
}
