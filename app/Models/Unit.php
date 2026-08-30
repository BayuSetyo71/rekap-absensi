<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'code',
        'name',
        'color',
        'default_time_in',
        'default_time_out',
        'default_late_tolerance',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_late_tolerance' => 'integer',
    ];

    /**
     * Relasi ke jadwal kerja standar per hari di unit ini
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(UnitWorkSchedule::class, 'unit_id')->orderBy('day_of_week', 'asc');
    }

    /**
     * Relasi ke seluruh pegawai / guru yang mengajar di unit ini
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_units', 'unit_id', 'user_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Relasi ke daftar tarif honor mengajar di unit ini
     */
    public function teachingRates(): HasMany
    {
        return $this->hasMany(TeachingRate::class, 'unit_id');
    }

    /**
     * Helper badge HTML untuk UI
     */
    public function getBadgeHtmlAttribute(): string
    {
        $color = $this->color ?: '#4f46e5';
        return '<span class="badge rounded-pill px-2.5 py-1" style="background-color: ' . $color . '20; color: ' . $color . '; border: 1px solid ' . $color . '40;">'
            . '<i class="bi bi-mortarboard-fill me-1"></i>' . e($this->name)
            . '</span>';
    }

    /**
     * Format jam masuk (HH:mm)
     */
    public function getFormattedTimeInAttribute(): string
    {
        return $this->default_time_in ? substr($this->default_time_in, 0, 5) : '-';
    }

    /**
     * Format jam pulang (HH:mm)
     */
    public function getFormattedTimeOutAttribute(): string
    {
        return $this->default_time_out ? substr($this->default_time_out, 0, 5) : '-';
    }
}
