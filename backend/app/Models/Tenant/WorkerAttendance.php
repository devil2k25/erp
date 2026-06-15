<?php

namespace App\Models\Tenant;

class WorkerAttendance extends TenantModel
{
    protected $table = 'worker_attendance';
    protected $fillable = [
        'user_id', 'shift_id', 'attendance_date', 'check_in_time', 'check_out_time',
        'status', 'overtime_minutes', 'break_minutes', 'notes', 'marked_by',
    ];
    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime', 'check_out_time' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function shift() { return $this->belongsTo(Shift::class); }
    public function marker() { return $this->belongsTo(User::class, 'marked_by'); }
}
