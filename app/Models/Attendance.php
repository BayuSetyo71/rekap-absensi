<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    /**
     * Relasi ke data pengguna / pegawai
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke admin / user yang menginjek data
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Format tanggal Indonesia (contoh: Senin, 30 Agu 2026)
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->attendance_date ? $this->attendance_date->translatedFormat('d M Y') : '-';
    }

    /**
     * Format jam masuk (HH:mm)
     */
    public function getFormattedCheckInAttribute(): string
    {
        return $this->check_in ? substr($this->check_in, 0, 5) : '-';
    }

    /**
     * Format jam pulang (HH:mm)
     */
    public function getFormattedCheckOutAttribute(): string
    {
        return $this->check_out ? substr($this->check_out, 0, 5) : '-';
    }

    /**
     * Helper badge warna status kehadiran
     */
    public function getStatusBadgeAttribute(): string
    {
        return match (strtolower($this->status)) {
            'hadir' => '<span class="badge badge-subtle-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Hadir</span>',
            'terlambat' => '<span class="badge badge-subtle-warning px-2 py-1"><i class="bi bi-clock-history me-1"></i>Terlambat</span>',
            'izin' => '<span class="badge badge-subtle-info px-2 py-1"><i class="bi bi-info-circle me-1"></i>Izin</span>',
            'sakit' => '<span class="badge badge-subtle-primary px-2 py-1"><i class="bi bi-hospital me-1"></i>Sakit</span>',
            'alpa' => '<span class="badge badge-subtle-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Alpa</span>',
            default => '<span class="badge bg-secondary px-2 py-1">' . ucfirst($this->status) . '</span>',
        };
    }
}
