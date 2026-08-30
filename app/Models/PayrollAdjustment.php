<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    use HasFactory;

    protected $table = 'payroll_adjustments';

    protected $fillable = [
        'payroll_id',
        'type',
        'name',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi ke header payroll
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    /**
     * Format rupiah nominal penyesuaian
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Badge status jenis penyesuaian (tunjangan vs potongan)
     */
    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'allowance'
            ? '<span class="badge badge-subtle-success px-2 py-1"><i class="bi bi-plus-circle me-1"></i>Tunjangan</span>'
            : '<span class="badge badge-subtle-danger px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Potongan</span>';
    }
}
