<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkSchedule extends Model
{
    use HasFactory;

    protected $table = 'employee_work_schedules';

    protected $fillable = [
        'user_id',
        'day_of_week',
        'day_name',
        'unit_id',
        'schedule_type',
        'time_in',
        'time_out',
        'late_tolerance_minutes',
        'is_day_off',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'late_tolerance_minutes' => 'integer',
        'is_day_off' => 'boolean',
    ];

    /**
     * Relasi ke user / pegawai
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke unit tempat mengajar pada hari tersebut
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Format jam masuk (HH:mm)
     */
    public function getFormattedTimeInAttribute(): string
    {
        return $this->time_in ? substr($this->time_in, 0, 5) : '-';
    }

    /**
     * Format jam pulang (HH:mm)
     */
    public function getFormattedTimeOutAttribute(): string
    {
        return $this->time_out ? substr($this->time_out, 0, 5) : '-';
    }
}
