<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitWorkSchedule extends Model
{
    use HasFactory;

    protected $table = 'unit_work_schedules';

    protected $fillable = [
        'unit_id',
        'day_of_week',
        'day_name',
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
     * Relasi ke unit induk
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
