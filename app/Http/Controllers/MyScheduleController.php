<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyScheduleController extends Controller
{
    /**
     * Daftar nama hari dalam bahasa Indonesia (1 = Senin s.d. 7 = Minggu)
     */
    protected array $dayNames = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Menampilkan Jadwal Mengajar Perorangan (Senin s.d. Minggu) Berdasarkan Akun Login
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load(['units', 'teachingSlots.unit', 'workSchedules.unit']);

        $todayIso = (int)Carbon::now()->dayOfWeekIso; // 1 = Senin s.d. 7 = Minggu
        $slots = $user->teachingSlots;

        // Susun matriks jadwal 7 hari (Senin s.d. Minggu)
        $scheduleByDay = [];
        $totalWeeklyMinutes = 0;
        $totalWeeklySlots = $slots->count();

        foreach ($this->dayNames as $dayNum => $dayName) {
            $daySlots = $slots->where('day_of_week', $dayNum)->sortBy('start_time')->values();
            $dayMinutes = $daySlots->sum(fn($s) => $s->duration_minutes);
            $totalWeeklyMinutes += $dayMinutes;

            $workSchedule = $user->getWorkScheduleForDay($dayNum);

            $scheduleByDay[$dayNum] = [
                'day_num'         => $dayNum,
                'day_name'        => $dayName,
                'is_today'        => ($dayNum === $todayIso),
                'slots'           => $daySlots,
                'total_slots'     => $daySlots->count(),
                'total_minutes'   => $dayMinutes,
                'total_hours'     => round($dayMinutes / 60, 1),
                'work_schedule'   => $workSchedule,
                'is_day_off'      => $workSchedule->is_day_off && $daySlots->isEmpty(),
            ];
        }

        // Metrik Ringkasan Mingguan
        $summary = [
            'total_slots'        => $totalWeeklySlots,
            'total_minutes'      => $totalWeeklyMinutes,
            'total_hours'        => round($totalWeeklyMinutes / 60, 1),
            'today_iso'          => $todayIso,
            'today_name'         => $this->dayNames[$todayIso] ?? 'Hari Ini',
            'today_slots_count'  => $scheduleByDay[$todayIso]['total_slots'] ?? 0,
            'today_hours'        => $scheduleByDay[$todayIso]['total_hours'] ?? 0,
            'units_count'        => $user->units->count(),
            'units_list'         => $user->units,
        ];

        return view('my-schedule.index', compact('user', 'scheduleByDay', 'summary'));
    }
}
