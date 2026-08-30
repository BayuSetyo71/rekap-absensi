<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingRate extends Model
{
    use HasFactory;

    protected $table = 'teaching_rates';

    protected $fillable = [
        'unit_id',
        'subject_name',
        'rate_per_hour',
        'rate_type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'rate_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke unit / jenjang sekolah (TK, SD, SMP, SMA, Yayasan)
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Format rupiah untuk nominal tarif
     */
    public function getFormattedRateAttribute(): string
    {
        return 'Rp ' . number_format($this->rate_per_hour, 0, ',', '.');
    }
}
