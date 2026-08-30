<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'user_id',
        'period_month',
        'total_present_days',
        'total_sessions_taught',
        'total_hours_taught',
        'gross_teaching_amount',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'status',
        'notes',
        'paid_at',
        'processed_by',
    ];

    protected $casts = [
        'total_present_days' => 'integer',
        'total_sessions_taught' => 'integer',
        'total_hours_taught' => 'decimal:2',
        'gross_teaching_amount' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Relasi ke data guru / pegawai penerima gaji
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke admin pemroses payroll
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Relasi ke rincian sesi mengajar per unit & mapel
     */
    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id');
    }

    /**
     * Relasi ke daftar penyesuaian (tunjangan dan potongan)
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class, 'payroll_id');
    }

    /**
     * Tunjangan saja
     */
    public function allowances(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class, 'payroll_id')->where('type', 'allowance');
    }

    /**
     * Potongan saja
     */
    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class, 'payroll_id')->where('type', 'deduction');
    }

    /**
     * Format nama bulan dan tahun Indonesia (contoh: Agustus 2026)
     */
    public function getFormattedPeriodAttribute(): string
    {
        if (!$this->period_month) return '-';
        return Carbon::createFromFormat('Y-m', $this->period_month)->translatedFormat('F Y');
    }

    /**
     * Format rupiah untuk nilai honor kotor
     */
    public function getFormattedGrossAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_teaching_amount, 0, ',', '.');
    }

    /**
     * Format rupiah untuk total tunjangan
     */
    public function getFormattedTotalAllowancesAttribute(): string
    {
        return 'Rp ' . number_format($this->total_allowances, 0, ',', '.');
    }

    /**
     * Format rupiah untuk total potongan
     */
    public function getFormattedTotalDeductionsAttribute(): string
    {
        return 'Rp ' . number_format($this->total_deductions, 0, ',', '.');
    }

    /**
     * Format rupiah untuk gaji bersih (Take Home Pay)
     */
    public function getFormattedNetSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->net_salary, 0, ',', '.');
    }

    /**
     * Helper badge status penggajian (Draft, Locked, Paid)
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge badge-subtle-warning px-2.5 py-1 text-uppercase fw-semibold" style="font-size:0.72rem;"><i class="bi bi-pencil-square me-1"></i>Draft</span>',
            'locked' => '<span class="badge badge-subtle-info px-2.5 py-1 text-uppercase fw-semibold" style="font-size:0.72rem;"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>',
            'paid' => '<span class="badge badge-subtle-success px-2.5 py-1 text-uppercase fw-semibold" style="font-size:0.72rem;"><i class="bi bi-check2-circle me-1"></i>Dibayar</span>',
            default => '<span class="badge bg-secondary px-2.5 py-1">' . ucfirst($this->status) . '</span>',
        };
    }

    /**
     * Kalkulasi ulang total tunjangan, potongan, dan net salary
     */
    public function recalculateTotals(): void
    {
        $this->gross_teaching_amount = $this->details()->sum('subtotal');
        $this->total_allowances = $this->allowances()->sum('amount');
        $this->total_deductions = $this->deductions()->sum('amount');
        $this->net_salary = max(0, $this->gross_teaching_amount + $this->total_allowances - $this->total_deductions);
        $this->total_sessions_taught = $this->details()->sum('total_sessions');
        $this->total_hours_taught = $this->details()->sum('total_hours');
        $this->save();
    }
}
