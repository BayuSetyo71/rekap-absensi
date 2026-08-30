<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTeachingSlot extends Model
{
    use HasFactory;

    protected $table = 'employee_teaching_slots';

    protected $fillable = [
        'user_id',
        'day_of_week',
        'unit_id',
        'start_time',
        'end_time',
        'subject',
        'notes',
        'order_index',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'order_index' => 'integer',
    ];

    /**
     * Relasi ke data user / guru
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke unit jenjang sekolah (TK, SD, SMP, SMA, Yayasan)
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Format jam mulai (HH:mm)
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? substr($this->start_time, 0, 5) : '-';
    }

    /**
     * Format jam selesai (HH:mm)
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? substr($this->end_time, 0, 5) : '-';
    }

    /**
     * Durasi mengajar dalam menit
     */
    public function getDurationMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $startParts = explode(':', $this->start_time);
        $endParts = explode(':', $this->end_time);

        $startMins = ((int)$startParts[0] * 60) + (int)($startParts[1] ?? 0);
        $endMins = ((int)$endParts[0] * 60) + (int)($endParts[1] ?? 0);

        return max(0, $endMins - $startMins);
    }
}
