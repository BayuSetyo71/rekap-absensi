<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $table = 'payroll_details';

    protected $fillable = [
        'payroll_id',
        'unit_id',
        'subject',
        'total_sessions',
        'total_hours',
        'rate_applied',
        'subtotal',
    ];

    protected $casts = [
        'total_sessions' => 'integer',
        'total_hours' => 'decimal:2',
        'rate_applied' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relasi ke header payroll
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    /**
     * Relasi ke unit / jenjang sekolah
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Format rupiah untuk tarif yang diterapkan
     */
    public function getFormattedRateAttribute(): string
    {
        return 'Rp ' . number_format($this->rate_applied, 0, ',', '.');
    }

    /**
     * Format rupiah untuk subtotal honor
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
