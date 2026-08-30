<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeTeachingSlot;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\TeachingRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollCalculationService
{
    /**
     * Cache tarif honor dalam memori untuk mempercepat perhitungan massal
     */
    protected array $rateCache = [];

    /**
     * Menghitung dan menyimpan payroll untuk satu orang guru pada periode bulan tertentu
     *
     * @param User $user
     * @param string $periodMonth (Format: YYYY-MM, misal: '2026-08')
     * @param int|null $processedBy ID admin pemroses
     * @param bool $forceOverwrite Apakah memaksa overwrite jika status sudah locked/paid
     * @return Payroll
     */
    public function calculateForUser(User $user, string $periodMonth, ?int $processedBy = null, bool $forceOverwrite = false): Payroll
    {
        return DB::transaction(function () use ($user, $periodMonth, $processedBy, $forceOverwrite) {
            $startDate = Carbon::createFromFormat('Y-m', $periodMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $periodMonth)->endOfMonth();

            // 1. Cek payroll yang sudah ada
            $payroll = Payroll::firstOrNew([
                'user_id' => $user->id,
                'period_month' => $periodMonth,
            ]);

            // Jika sudah locked/paid dan tidak force overwrite, lewati update detail
            if ($payroll->exists && in_array($payroll->status, ['locked', 'paid']) && !$forceOverwrite) {
                return $payroll;
            }

            // 2. Ambil data kehadiran guru di bulan tersebut (Hadir & Terlambat diakui)
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['hadir', 'terlambat'])
                ->get();

            $totalPresentDays = $attendances->count();

            // 3. Ambil seluruh konfigurasi sesi mengajar guru
            $teachingSlots = EmployeeTeachingSlot::with('unit')
                ->where('user_id', $user->id)
                ->get()
                ->groupBy('day_of_week');

            // 4. Kalkulasi sesi mengajar per hari kehadiran
            $breakdown = []; // [groupKey => ['unit_id', 'subject', 'sessions', 'hours', 'rate', 'subtotal']]

            foreach ($attendances as $att) {
                $dayOfWeek = (int)$att->attendance_date->format('N'); // 1=Senin ... 7=Minggu
                $slots = $teachingSlots->get($dayOfWeek, collect());

                foreach ($slots as $slot) {
                    $unitId = $slot->unit_id;
                    $subject = trim($slot->subject ?: 'Mata Pelajaran Umum');
                    $durationHours = round($slot->duration_minutes / 60, 2);
                    $ratePerHour = $this->getTeachingRate($unitId, $subject);
                    $subtotal = round($durationHours * $ratePerHour, 2);

                    $key = ($unitId ?? 0) . '||' . strtolower($subject) . '||' . $ratePerHour;

                    if (!isset($breakdown[$key])) {
                        $breakdown[$key] = [
                            'unit_id' => $unitId,
                            'subject' => $subject,
                            'total_sessions' => 0,
                            'total_hours' => 0,
                            'rate_applied' => $ratePerHour,
                            'subtotal' => 0,
                        ];
                    }

                    $breakdown[$key]['total_sessions'] += 1;
                    $breakdown[$key]['total_hours'] += $durationHours;
                    $breakdown[$key]['subtotal'] += $subtotal;
                }
            }

            // 5. Simpan / Perbarui Header Payroll
            $totalSessions = array_sum(array_column($breakdown, 'total_sessions'));
            $totalHours = array_sum(array_column($breakdown, 'total_hours'));
            $grossTeachingAmount = array_sum(array_column($breakdown, 'subtotal'));

            $payroll->total_present_days = $totalPresentDays;
            $payroll->total_sessions_taught = $totalSessions;
            $payroll->total_hours_taught = $totalHours;
            $payroll->gross_teaching_amount = $grossTeachingAmount;
            if (!$payroll->exists) {
                $payroll->status = 'draft';
            }
            if ($processedBy) {
                $payroll->processed_by = $processedBy;
            }
            $payroll->save();

            // 6. Perbarui Rincian Detail Sesi Mengajar
            $payroll->details()->delete();

            foreach ($breakdown as $item) {
                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'unit_id' => $item['unit_id'],
                    'subject' => $item['subject'],
                    'total_sessions' => $item['total_sessions'],
                    'total_hours' => $item['total_hours'],
                    'rate_applied' => $item['rate_applied'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 7. Hitung total akhir dengan penyesuaian (tunjangan & potongan)
            $payroll->recalculateTotals();

            return $payroll;
        });
    }

    /**
     * Hitung massal untuk seluruh guru aktif pada periode bulan tertentu
     *
     * @param string $periodMonth
     * @param int|null $processedBy
     * @return array
     */
    public function calculateAll(string $periodMonth, ?int $processedBy = null): array
    {
        $users = User::where('is_active', true)
            ->whereHas('teachingSlots')
            ->get();

        // Jika tidak ada guru dengan slot khusus, ambil semua user aktif
        if ($users->isEmpty()) {
            $users = User::where('is_active', true)->get();
        }

        $processedCount = 0;
        $totalGross = 0;
        $totalNet = 0;

        foreach ($users as $user) {
            try {
                $payroll = $this->calculateForUser($user, $periodMonth, $processedBy);
                $processedCount++;
                $totalGross += $payroll->gross_teaching_amount;
                $totalNet += $payroll->net_salary;
            } catch (\Exception $e) {
                Log::error("Gagal kalkulasi payroll user ID {$user->id}: " . $e->getMessage());
            }
        }

        return [
            'total_users' => $processedCount,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'period_month' => $periodMonth,
        ];
    }

    /**
     * Mengambil tarif honor per jam berdasarkan unit dan nama mapel
     *
     * @param int|null $unitId
     * @param string $subject
     * @return float
     */
    public function getTeachingRate(?int $unitId, string $subject): float
    {
        if (!$unitId) {
            return 35000.00; // Default fallback honor standar yayasan
        }

        $cacheKey = "{$unitId}_{$subject}";
        if (isset($this->rateCache[$cacheKey])) {
            return $this->rateCache[$cacheKey];
        }

        // 1. Cek tarif spesifik untuk mapel ini di unit bersangkutan
        $rate = TeachingRate::where('unit_id', $unitId)
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(subject_name)) = ?', [strtolower(trim($subject))])
            ->first();

        if ($rate) {
            $this->rateCache[$cacheKey] = (float)$rate->rate_per_hour;
            return (float)$rate->rate_per_hour;
        }

        // 2. Jika tidak ada tarif spesifik mapel, gunakan tarif 'DEFAULT' jenjang/unit tersebut
        $defaultUnitRate = TeachingRate::where('unit_id', $unitId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('subject_name', 'DEFAULT')
                  ->orWhere('subject_name', 'All')
                  ->orWhere('subject_name', 'Semua');
            })
            ->first();

        if ($defaultUnitRate) {
            $this->rateCache[$cacheKey] = (float)$defaultUnitRate->rate_per_hour;
            return (float)$defaultUnitRate->rate_per_hour;
        }

        // 3. Fallback jika belum disetting sama sekali
        $this->rateCache[$cacheKey] = 35000.00;
        return 35000.00;
    }
}
