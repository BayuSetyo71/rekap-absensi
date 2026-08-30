<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceRecapController extends Controller
{
    /**
     * Ambang batas jam masuk tepat waktu (HH:MM:SS) -> default 07:30:00 (450 menit)
     */
    protected string $onTimeThreshold = '07:30:00';

    /**
     * Konversi string waktu "HH:MM:SS" atau "HH:MM" ke integer menit dari jam 00:00
     */
    protected function timeToMinutes(?string $timeStr): ?int
    {
        if (!$timeStr) {
            return null;
        }
        $parts = explode(':', $timeStr);
        return ((int)$parts[0] * 60) + (int)($parts[1] ?? 0);
    }

    /**
     * Format total menit ke format jam & menit (contoh: 4j 34m atau 25m)
     */
    protected function formatMinutes(int $minutes, bool $showZeroAsDash = false): string
    {
        if ($minutes <= 0) {
            return $showZeroAsDash ? '-' : '0m';
        }
        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;
        if ($hours > 0) {
            return "{$hours}j {$mins}m";
        }
        return "{$mins}m";
    }

    /**
     * Mengambil ambang batas jam masuk (dalam menit) spesifik pegawai sesuai jadwal harian
     */
    protected function getEmployeeThresholdMinutes(User $user, $date): int
    {
        $sch = $user->getWorkScheduleForDate($date);
        $timeIn = $sch->time_in ?: $this->onTimeThreshold;
        return $this->timeToMinutes($timeIn) ?? 450;
    }

    /**
     * Menampilkan halaman rekapitulasi absensi per pegawai
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        // Penentuan rentang tanggal default (Awal bulan s.d. Akhir bulan ini)
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        // Query dasar data pegawai
        $userQuery = User::with('role')->where('is_active', true);

        // Jika user bukan Superadmin & tidak punya izin kelola users (karyawan/guru biasa), langsung arahkan ke grafik kehadiran pribadinya
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view')) {
            return redirect()->route('attendance-recap.chart', [
                'user'       => $currentUser->id,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);
        }

        // Filter Departemen
        if ($request->filled('department')) {
            $userQuery->where('department', $request->input('department'));
        }

        // Filter Pencarian Nama / NIP / Email / Jabatan
        if ($request->filled('search')) {
            $search = $request->input('search');
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $employees = $userQuery->orderBy('name', 'asc')->get();

        // Ambil data absensi seluruh pegawai yang relevan pada rentang tanggal
        $employeeIds = $employees->pluck('id');
        
        $attendances = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $thresholdMinutes = $this->timeToMinutes($this->onTimeThreshold); // 450 menit (07:30)

        // Agregasi statistik per pegawai
        $recapData = $employees->map(function ($emp) use ($attendances) {
            $userAttendances = $attendances->get($emp->id, collect());
            
            $totalLogs     = $userAttendances->count();
            $checkInCount  = $userAttendances->whereNotNull('check_in')->count();
            $checkOutCount = $userAttendances->whereNotNull('check_out')->count();
            
            $onTimeCount       = 0;
            $lateCount         = 0;
            $permitCount       = 0;
            $sickCount         = 0;
            $alphaCount        = 0;
            $totalLateMinutes  = 0;
            $totalEarlyMinutes = 0;
            $earlyCount        = 0;

            foreach ($userAttendances as $item) {
                $thresholdMinutes = $this->getEmployeeThresholdMinutes($emp, $item->attendance_date);

                if ($item->status === 'izin') {
                    $permitCount++;
                } elseif ($item->status === 'sakit') {
                    $sickCount++;
                } elseif ($item->status === 'alpa') {
                    $alphaCount++;
                } elseif ($item->status === 'terlambat' || ($item->check_in && $this->timeToMinutes($item->check_in) > $thresholdMinutes)) {
                    $lateCount++;
                } else {
                    $onTimeCount++;
                }

                // Kalkulasi menit keterlambatan & menit datang lebih awal secara presisi
                if ($item->check_in) {
                    $inMinutes = $this->timeToMinutes($item->check_in);
                    if ($inMinutes !== null) {
                        if ($inMinutes > $thresholdMinutes) {
                            $totalLateMinutes += ($inMinutes - $thresholdMinutes);
                        } elseif ($inMinutes < $thresholdMinutes) {
                            $totalEarlyMinutes += ($thresholdMinutes - $inMinutes);
                            $earlyCount++;
                        }
                    }
                }
            }

            // Persentase kehadiran: (Hadir + Terlambat) / Total Catatan * 100
            $attendancePercentage = $totalLogs > 0 
                ? min(100, round((($onTimeCount + $lateCount) / $totalLogs) * 100, 1)) 
                : 0;

            return (object) [
                'user'                  => $emp,
                'total_logs'            => $totalLogs,
                'check_in_count'        => $checkInCount,
                'check_out_count'       => $checkOutCount,
                'on_time_count'         => $onTimeCount,
                'late_count'            => $lateCount,
                'early_count'           => $earlyCount,
                'permit_count'          => $permitCount,
                'sick_count'            => $sickCount,
                'alpha_count'           => $alphaCount,
                'total_late_minutes'    => $totalLateMinutes,
                'total_late_formatted'  => $this->formatMinutes($totalLateMinutes),
                'total_early_minutes'   => $totalEarlyMinutes,
                'total_early_formatted' => $this->formatMinutes($totalEarlyMinutes, true),
                'attendance_percentage' => $attendancePercentage,
            ];
        });

        // Filter status persentase / kehadiran jika ada filter khusus
        if ($request->filled('performance')) {
            $perf = $request->input('performance');
            if ($perf === 'excellent') {
                $recapData = $recapData->where('attendance_percentage', '>=', 95);
            } elseif ($perf === 'good') {
                $recapData = $recapData->whereBetween('attendance_percentage', [80, 94.9]);
            } elseif ($perf === 'warning') {
                $recapData = $recapData->where('attendance_percentage', '<', 80);
            } elseif ($perf === 'frequent_late') {
                $recapData = $recapData->where('late_count', '>', 0)->sortByDesc('late_count');
            } elseif ($perf === 'champion_early') {
                $recapData = $recapData->where('total_early_minutes', '>', 0)->sortByDesc('total_early_minutes');
            }
        }

        // Ringkasan KPI Global untuk Header Banner
        $kpi = [
            'total_employees'            => $recapData->count(),
            'avg_attendance'             => $recapData->count() > 0 ? round($recapData->avg('attendance_percentage'), 1) : 0,
            'total_check_in'             => $recapData->sum('check_in_count'),
            'total_check_out'            => $recapData->sum('check_out_count'),
            'total_late'                 => $recapData->sum('late_count'),
            'total_late_minutes'         => $recapData->sum('total_late_minutes'),
            'total_late_time_formatted'  => $this->formatMinutes($recapData->sum('total_late_minutes')),
            'total_early_minutes'        => $recapData->sum('total_early_minutes'),
            'total_early_time_formatted' => $this->formatMinutes($recapData->sum('total_early_minutes')),
            'total_on_time'              => $recapData->sum('on_time_count'),
            'total_permit'               => $recapData->sum('permit_count'),
            'total_sick'                 => $recapData->sum('sick_count'),
            'total_alpha'                => $recapData->sum('alpha_count'),
        ];

        // Daftar departemen untuk dropdown filter
        $departments = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        return view('attendance-recap.index', compact(
            'recapData',
            'kpi',
            'departments',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Mengambil detail riwayat absensi harian per pegawai untuk modal dialog (AJAX)
     */
    public function detailAjax(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view') && $currentUser->id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk melihat data pegawai ini.',
            ], 403);
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->get();

        $totalLogs     = $attendances->count();
        $checkInCount  = $attendances->whereNotNull('check_in')->count();
        $checkOutCount = $attendances->whereNotNull('check_out')->count();
        
        $onTimeCount       = 0;
        $lateCount         = 0;
        $permitCount       = 0;
        $sickCount         = 0;
        $alphaCount        = 0;
        $totalLateMinutes  = 0;
        $totalEarlyMinutes = 0;
        $thresholdMinutes  = $this->timeToMinutes($this->onTimeThreshold);

        foreach ($attendances as $item) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);

            if ($item->status === 'izin') {
                $permitCount++;
            } elseif ($item->status === 'sakit') {
                $sickCount++;
            } elseif ($item->status === 'alpa') {
                $alphaCount++;
            } elseif ($item->status === 'terlambat' || ($item->check_in && $this->timeToMinutes($item->check_in) > $thresholdMinutes)) {
                $lateCount++;
            } else {
                $onTimeCount++;
            }

            if ($item->check_in) {
                $inMinutes = $this->timeToMinutes($item->check_in);
                if ($inMinutes !== null) {
                    if ($inMinutes > $thresholdMinutes) {
                        $totalLateMinutes += ($inMinutes - $thresholdMinutes);
                    } elseif ($inMinutes < $thresholdMinutes) {
                        $totalEarlyMinutes += ($thresholdMinutes - $inMinutes);
                    }
                }
            }
        }

        $attendancePercentage = $totalLogs > 0 
            ? min(100, round((($onTimeCount + $lateCount) / $totalLogs) * 100, 1)) 
            : 0;

        // Transform data log harian
        $logs = $attendances->map(function ($item) use ($user) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);
            $formattedDate = $item->attendance_date 
                ? Carbon::parse($item->attendance_date)->translatedFormat('l, d M Y') 
                : '-';

            $checkIn  = $item->check_in ? substr($item->check_in, 0, 5) : '-';
            $checkOut = $item->check_out ? substr($item->check_out, 0, 5) : '-';

            $workDuration = '-';
            if ($item->check_in && $item->check_out) {
                $inMinutes  = $this->timeToMinutes($item->check_in);
                $outMinutes = $this->timeToMinutes($item->check_out);
                if ($outMinutes >= $inMinutes) {
                    $diffMins = $outMinutes - $inMinutes;
                    $hours    = floor($diffMins / 60);
                    $mins     = $diffMins % 60;
                    $workDuration = "{$hours}j {$mins}m";
                }
            }

            $lateMinutes  = 0;
            $earlyMinutes = 0;
            if ($item->check_in) {
                $inMinutes = $this->timeToMinutes($item->check_in);
                if ($inMinutes !== null) {
                    if ($inMinutes > $thresholdMinutes) {
                        $lateMinutes = $inMinutes - $thresholdMinutes;
                    } elseif ($inMinutes < $thresholdMinutes) {
                        $earlyMinutes = $thresholdMinutes - $inMinutes;
                    }
                }
            }

            return [
                'id'              => $item->id,
                'date_raw'        => $item->attendance_date ? $item->attendance_date->toDateString() : '',
                'date_formatted'  => $formattedDate,
                'check_in'        => $checkIn,
                'check_out'       => $checkOut,
                'work_duration'   => $workDuration,
                'late_minutes'    => $lateMinutes,
                'early_minutes'   => $earlyMinutes,
                'status'          => $item->status,
                'status_badge'    => $item->status_badge,
                'notes'           => $item->notes ?: '-',
            ];
        });

        return response()->json([
            'status' => 'success',
            'user'   => [
                'id'         => $user->id,
                'name'       => $user->name,
                'nip'        => $user->nip ?: '-',
                'position'   => $user->position ?: '-',
                'department' => $user->department ?: '-',
                'email'      => $user->email,
                'phone'      => $user->phone ?: '-',
                'avatar_url' => $user->avatar_url,
                'role_name'  => $user->role?->display_name ?? 'Pegawai',
            ],
            'period' => [
                'start_date' => Carbon::parse($startDate)->translatedFormat('d M Y'),
                'end_date'   => Carbon::parse($endDate)->translatedFormat('d M Y'),
            ],
            'stats'  => [
                'total_logs'            => $totalLogs,
                'check_in_count'        => $checkInCount,
                'check_out_count'       => $checkOutCount,
                'on_time_count'         => $onTimeCount,
                'late_count'            => $lateCount,
                'permit_count'          => $permitCount,
                'sick_count'            => $sickCount,
                'alpha_count'           => $alphaCount,
                'total_late_formatted'  => $this->formatMinutes($totalLateMinutes),
                'total_early_formatted' => $this->formatMinutes($totalEarlyMinutes),
                'attendance_percentage' => $attendancePercentage,
            ],
            'logs'   => $logs,
        ]);
    }

    /**
     * Mengambil data multi-grafik analitik lengkap per pegawai (Chart.js AJAX)
     */
    public function chartDataAjax(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view') && $currentUser->id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'asc')
            ->get();

        $onTimeCount = 0;
        $lateCount   = 0;
        $permitCount = 0;
        $sickCount   = 0;
        $alphaCount  = 0;

        foreach ($attendances as $item) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);
            if ($item->status === 'izin') {
                $permitCount++;
            } elseif ($item->status === 'sakit') {
                $sickCount++;
            } elseif ($item->status === 'alpa') {
                $alphaCount++;
            } elseif ($item->status === 'terlambat' || ($item->check_in && $this->timeToMinutes($item->check_in) > $thresholdMinutes)) {
                $lateCount++;
            } else {
                $onTimeCount++;
            }
        }

        $statusChart = [
            'labels'   => ['Tepat Waktu', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            'datasets' => [
                [
                    'data'            => [$onTimeCount, $lateCount, $permitCount, $sickCount, $alphaCount],
                    'backgroundColor' => ['#10b981', '#f59e0b', '#06b6d4', '#4f46e5', '#ef4444'],
                    'hoverOffset'     => 6,
                ]
            ],
        ];

        // 2. Data Tren Jam Check-In & Check-Out Harian
        $timelineLabels = [];
        $checkInHours   = [];
        $checkOutHours  = [];
        $thresholdIn    = [];
        $thresholdOut   = [];
        $workHoursArray = [];

        $totalInMinutes    = 0;
        $countIn           = 0;
        $totalOutMinutes   = 0;
        $countOut          = 0;
        $totalWorkMinutes  = 0;
        $totalLateMinutes  = 0;
        $totalEarlyMinutes = 0;

        foreach ($attendances as $item) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);
            $sch = $user->getWorkScheduleForDate($item->attendance_date);
            $schInDecimal = $sch->time_in ? round((float)explode(':', $sch->time_in)[0] + ((float)explode(':', $sch->time_in)[1] / 60), 2) : 7.5;
            $schOutDecimal = $sch->time_out ? round((float)explode(':', $sch->time_out)[0] + ((float)explode(':', $sch->time_out)[1] / 60), 2) : 14.0;

            $label = $item->attendance_date ? Carbon::parse($item->attendance_date)->format('d M') : '';
            $timelineLabels[] = $label;

            $thresholdIn[]  = $schInDecimal;
            $thresholdOut[] = $schOutDecimal;

            // Check-in calculation
            if ($item->check_in) {
                $timeParts    = explode(':', $item->check_in);
                $hourDecimal  = round((float)$timeParts[0] + ((float)$timeParts[1] / 60), 2);
                $checkInHours[] = $hourDecimal;

                $mins = ((int)$timeParts[0] * 60) + (int)$timeParts[1];
                $totalInMinutes += $mins;
                $countIn++;

                if ($mins > $thresholdMinutes) {
                    $totalLateMinutes += ($mins - $thresholdMinutes);
                } elseif ($mins < $thresholdMinutes) {
                    $totalEarlyMinutes += ($thresholdMinutes - $mins);
                }
            } else {
                $checkInHours[] = null;
            }

            // Check-out calculation
            if ($item->check_out) {
                $timePartsOut   = explode(':', $item->check_out);
                $hourDecimalOut = round((float)$timePartsOut[0] + ((float)$timePartsOut[1] / 60), 2);
                $checkOutHours[] = $hourDecimalOut;

                $minsOut = ((int)$timePartsOut[0] * 60) + (int)$timePartsOut[1];
                $totalOutMinutes += $minsOut;
                $countOut++;
            } else {
                $checkOutHours[] = null;
            }

            // Work Duration calculation
            if ($item->check_in && $item->check_out) {
                $inMin  = $this->timeToMinutes($item->check_in);
                $outMin = $this->timeToMinutes($item->check_out);
                if ($outMin >= $inMin) {
                    $diffMins = $outMin - $inMin;
                    $totalWorkMinutes += $diffMins;
                    $workHoursArray[] = round($diffMins / 60, 1);
                } else {
                    $workHoursArray[] = 0;
                }
            } else {
                $workHoursArray[] = 0;
            }
        }

        $timelineChart = [
            'labels'   => $timelineLabels,
            'datasets' => [
                [
                    'label'           => 'Jam Scan Masuk',
                    'data'            => $checkInHours,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.12)',
                    'tension'         => 0.25,
                    'fill'            => false,
                    'pointRadius'     => 4,
                    'pointHoverRadius'=> 6,
                ],
                [
                    'label'           => 'Jam Scan Pulang',
                    'data'            => $checkOutHours,
                    'borderColor'     => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.12)',
                    'tension'         => 0.25,
                    'fill'            => false,
                    'pointRadius'     => 4,
                    'pointHoverRadius'=> 6,
                ],
                [
                    'label'           => 'Batas Masuk (07:30)',
                    'data'            => $thresholdIn,
                    'borderColor'     => '#f59e0b',
                    'borderDash'      => [6, 4],
                    'pointRadius'     => 0,
                    'fill'            => false,
                ],
            ],
        ];

        // 3. Data Distribusi Kehadiran Berdasarkan Hari Kerja (Senin - Sabtu)
        $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $dayLabels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayOnTime = array_fill_keys($dayLabels, 0);
        $dayLate   = array_fill_keys($dayLabels, 0);
        $dayAbsence= array_fill_keys($dayLabels, 0);

        foreach ($attendances as $item) {
            if ($item->attendance_date) {
                $engDay = $item->attendance_date->format('l');
                if (isset($dayNames[$engDay])) {
                    $indoDay = $dayNames[$engDay];
                    if ($item->status === 'terlambat' || ($item->check_in && $item->check_in > $this->onTimeThreshold)) {
                        $dayLate[$indoDay]++;
                    } elseif ($item->status === 'hadir' || ($item->check_in && $item->check_in <= $this->onTimeThreshold)) {
                        $dayOnTime[$indoDay]++;
                    } else {
                        $dayAbsence[$indoDay]++;
                    }
                }
            }
        }

        $dayDistributionChart = [
            'labels'   => $dayLabels,
            'datasets' => [
                [
                    'label'           => 'Tepat Waktu',
                    'data'            => array_values($dayOnTime),
                    'backgroundColor' => '#10b981',
                    'borderRadius'    => 6,
                ],
                [
                    'label'           => 'Terlambat',
                    'data'            => array_values($dayLate),
                    'backgroundColor' => '#f59e0b',
                    'borderRadius'    => 6,
                ],
                [
                    'label'           => 'Izin / Sakit / Alpa',
                    'data'            => array_values($dayAbsence),
                    'backgroundColor' => '#ef4444',
                    'borderRadius'    => 6,
                ],
            ],
        ];

        // 4. Data Durasi Jam Kerja Harian vs Target 8 Jam
        $target8Hours = array_fill(0, count($timelineLabels), 8.0);
        $workDurationChart = [
            'labels'   => $timelineLabels,
            'datasets' => [
                [
                    'label'           => 'Durasi Kerja (Jam)',
                    'data'            => $workHoursArray,
                    'backgroundColor' => '#3b82f6',
                    'borderRadius'    => 6,
                ],
                [
                    'type'            => 'line',
                    'label'           => 'Standar 8 Jam',
                    'data'            => $target8Hours,
                    'borderColor'     => '#10b981',
                    'borderDash'      => [5, 5],
                    'pointRadius'     => 0,
                    'fill'            => false,
                ]
            ],
        ];

        // Rata-rata Jam Masuk
        $avgCheckInStr = '-';
        if ($countIn > 0) {
            $avgMins = round($totalInMinutes / $countIn);
            $h = floor($avgMins / 60);
            $m = $avgMins % 60;
            $avgCheckInStr = sprintf('%02d:%02d WIB', $h, $m);
        }

        // Rata-rata Jam Pulang
        $avgCheckOutStr = '-';
        if ($countOut > 0) {
            $avgOutMins = round($totalOutMinutes / $countOut);
            $h = floor($avgOutMins / 60);
            $m = $avgOutMins % 60;
            $avgCheckOutStr = sprintf('%02d:%02d WIB', $h, $m);
        }

        // Rata-rata Durasi Kerja
        $avgWorkHoursStr = $countIn > 0 
            ? round(($totalWorkMinutes / $countIn) / 60, 1) . ' Jam / Hari'
            : '-';

        return response()->json([
            'status'        => 'success',
            'user'          => [
                'id'         => $user->id,
                'name'       => $user->name,
                'nip'        => $user->nip ?: '-',
                'position'   => $user->position ?: '-',
                'department' => $user->department ?: '-',
                'avatar_url' => $user->avatar_url,
            ],
            'period'        => [
                'start_date' => Carbon::parse($startDate)->translatedFormat('d M Y'),
                'end_date'   => Carbon::parse($endDate)->translatedFormat('d M Y'),
            ],
            'status_chart'          => $statusChart,
            'timeline_chart'        => $timelineChart,
            'day_distribution_chart'=> $dayDistributionChart,
            'work_duration_chart'   => $workDurationChart,
            'metrics'       => [
                'avg_check_in'        => $avgCheckInStr,
                'avg_check_out'       => $avgCheckOutStr,
                'avg_work_hours'      => $avgWorkHoursStr,
                'total_late_minutes'  => $this->formatMinutes($totalLateMinutes),
                'total_early_minutes' => $this->formatMinutes($totalEarlyMinutes),
                'total_logs'          => $attendances->count(),
            ],
        ]);
    }

    /**
     * Halaman penuh analitik visual & grafik per pegawai (Full Page Chart Analytics)
     */
    public function chartView(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view') && $currentUser->id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'asc')
            ->get();

        $totalLogs     = $attendances->count();
        $checkInCount  = $attendances->whereNotNull('check_in')->count();
        $checkOutCount = $attendances->whereNotNull('check_out')->count();

        $onTimeCount       = 0;
        $lateCount         = 0;
        $permitCount       = 0;
        $sickCount         = 0;
        $alphaCount        = 0;
        $totalLateMinutes  = 0;
        $totalEarlyMinutes = 0;
        $earlyCount        = 0;
        $thresholdMinutes  = $this->timeToMinutes($this->onTimeThreshold);

        foreach ($attendances as $item) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);

            if ($item->status === 'izin') {
                $permitCount++;
            } elseif ($item->status === 'sakit') {
                $sickCount++;
            } elseif ($item->status === 'alpa') {
                $alphaCount++;
            } elseif ($item->status === 'terlambat' || ($item->check_in && $this->timeToMinutes($item->check_in) > $thresholdMinutes)) {
                $lateCount++;
            } else {
                $onTimeCount++;
            }

            if ($item->check_in) {
                $inMinutes = $this->timeToMinutes($item->check_in);
                if ($inMinutes !== null) {
                    if ($inMinutes > $thresholdMinutes) {
                        $totalLateMinutes += ($inMinutes - $thresholdMinutes);
                    } elseif ($inMinutes < $thresholdMinutes) {
                        $totalEarlyMinutes += ($thresholdMinutes - $inMinutes);
                        $earlyCount++;
                    }
                }
            }
        }

        $attendancePercentage = $totalLogs > 0 
            ? min(100, round((($onTimeCount + $lateCount) / $totalLogs) * 100, 1)) 
            : 0;

        $stats = [
            'total_logs'            => $totalLogs,
            'check_in_count'        => $checkInCount,
            'check_out_count'       => $checkOutCount,
            'on_time_count'         => $onTimeCount,
            'late_count'            => $lateCount,
            'early_count'           => $earlyCount,
            'permit_count'          => $permitCount,
            'sick_count'            => $sickCount,
            'alpha_count'           => $alphaCount,
            'total_late_formatted'  => $this->formatMinutes($totalLateMinutes),
            'total_early_formatted' => $this->formatMinutes($totalEarlyMinutes),
            'attendance_percentage' => $attendancePercentage,
        ];

        return view('attendance-recap.chart', compact('user', 'attendances', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Halaman detail lengkap rekap absensi per pegawai (Full Page View Tabel)
     */
    public function show(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view') && $currentUser->id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $allUserAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $totalLogs     = $allUserAttendances->count();
        $checkInCount  = $allUserAttendances->whereNotNull('check_in')->count();
        $checkOutCount = $allUserAttendances->whereNotNull('check_out')->count();

        $onTimeCount       = 0;
        $lateCount         = 0;
        $permitCount       = 0;
        $sickCount         = 0;
        $alphaCount        = 0;
        $totalLateMinutes  = 0;
        $totalEarlyMinutes = 0;
        $thresholdMinutes  = $this->timeToMinutes($this->onTimeThreshold);

        foreach ($allUserAttendances as $item) {
            $thresholdMinutes = $this->getEmployeeThresholdMinutes($user, $item->attendance_date);

            if ($item->status === 'izin') {
                $permitCount++;
            } elseif ($item->status === 'sakit') {
                $sickCount++;
            } elseif ($item->status === 'alpa') {
                $alphaCount++;
            } elseif ($item->status === 'terlambat' || ($item->check_in && $this->timeToMinutes($item->check_in) > $thresholdMinutes)) {
                $lateCount++;
            } else {
                $onTimeCount++;
            }

            if ($item->check_in) {
                $inMinutes = $this->timeToMinutes($item->check_in);
                if ($inMinutes !== null) {
                    if ($inMinutes > $thresholdMinutes) {
                        $totalLateMinutes += ($inMinutes - $thresholdMinutes);
                    } elseif ($inMinutes < $thresholdMinutes) {
                        $totalEarlyMinutes += ($thresholdMinutes - $inMinutes);
                    }
                }
            }
        }

        $attendancePercentage = $totalLogs > 0 
            ? min(100, round((($onTimeCount + $lateCount) / $totalLogs) * 100, 1)) 
            : 0;

        $stats = [
            'total_logs'            => $totalLogs,
            'check_in_count'        => $checkInCount,
            'check_out_count'       => $checkOutCount,
            'on_time_count'         => $onTimeCount,
            'late_count'            => $lateCount,
            'permit_count'          => $permitCount,
            'sick_count'            => $sickCount,
            'alpha_count'           => $alphaCount,
            'total_late_formatted'  => $this->formatMinutes($totalLateMinutes),
            'total_early_formatted' => $this->formatMinutes($totalEarlyMinutes),
            'attendance_percentage' => $attendancePercentage,
        ];

        return view('attendance-recap.show', compact('user', 'attendances', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Ekspor rekapitulasi data absensi seluruh pegawai ke file Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $currentUser = Auth::user();
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $userQuery = User::with('role')->where('is_active', true);

        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('users', 'view')) {
            $userQuery->where('id', $currentUser->id);
        }

        if ($request->filled('department')) {
            $userQuery->where('department', $request->input('department'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees   = $userQuery->orderBy('name', 'asc')->get();
        $employeeIds = $employees->pluck('id');

        $attendances = Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absen Pegawai');

        // Header Title
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI ABSENSI PER PEGAWAI');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($startDate)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d M Y'));
        $sheet->setCellValue('A3', 'Dicetak pada: ' . now()->translatedFormat('l, d F Y H:i') . ' WIB oleh ' . $currentUser->name);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E1B4B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'));

        // Table Column Headers
        $headers = [
            'A5' => 'NO',
            'B5' => 'NIP',
            'C5' => 'NAMA PEGAWAI',
            'D5' => 'DEPARTEMEN',
            'E5' => 'JABATAN',
            'F5' => 'TOTAL HARI',
            'G5' => 'CHECK-IN',
            'H5' => 'CHECK-OUT',
            'I5' => 'TEPAT WAKTU',
            'J5' => 'DATANG LEBIH AWAL (KALI)',
            'K5' => 'WAKTU LEBIH AWAL',
            'L5' => 'TERLAMBAT (KALI)',
            'M5' => 'WAKTU TERLAMBAT',
            'N5' => 'IZIN',
            'O5' => 'SAKIT',
            'P5' => 'ALPA',
            'Q5' => '% KEHADIRAN',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E1B4B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6B7280']],
            ],
        ];
        $sheet->getStyle('A5:Q5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $row = 6;
        $no  = 1;
        $thresholdMinutes = $this->timeToMinutes($this->onTimeThreshold);

        foreach ($employees as $emp) {
            $userAttendances = $attendances->get($emp->id, collect());
            
            $totalLogs     = $userAttendances->count();
            $checkInCount  = $userAttendances->whereNotNull('check_in')->count();
            $checkOutCount = $userAttendances->whereNotNull('check_out')->count();
            
            $onTimeCount       = 0;
            $lateCount         = 0;
            $permitCount       = 0;
            $sickCount         = 0;
            $alphaCount        = 0;
            $totalLateMinutes  = 0;
            $totalEarlyMinutes = 0;
            $earlyCount        = 0;

            foreach ($userAttendances as $item) {
                if ($item->status === 'izin') {
                    $permitCount++;
                } elseif ($item->status === 'sakit') {
                    $sickCount++;
                } elseif ($item->status === 'alpa') {
                    $alphaCount++;
                } elseif ($item->status === 'terlambat' || ($item->check_in && $item->check_in > $this->onTimeThreshold)) {
                    $lateCount++;
                } else {
                    $onTimeCount++;
                }

                if ($item->check_in) {
                    $inMinutes = $this->timeToMinutes($item->check_in);
                    if ($inMinutes !== null) {
                        if ($inMinutes > $thresholdMinutes) {
                            $totalLateMinutes += ($inMinutes - $thresholdMinutes);
                        } elseif ($inMinutes < $thresholdMinutes) {
                            $totalEarlyMinutes += ($thresholdMinutes - $inMinutes);
                            $earlyCount++;
                        }
                    }
                }
            }

            $attendancePercentage = $totalLogs > 0 
                ? min(100, round((($onTimeCount + $lateCount) / $totalLogs) * 100, 1)) 
                : 0;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValueExplicit("B{$row}", $emp->nip ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->department ?: '-');
            $sheet->setCellValue("E{$row}", $emp->position ?: '-');
            $sheet->setCellValue("F{$row}", $totalLogs);
            $sheet->setCellValue("G{$row}", $checkInCount);
            $sheet->setCellValue("H{$row}", $checkOutCount);
            $sheet->setCellValue("I{$row}", $onTimeCount);
            $sheet->setCellValue("J{$row}", $earlyCount);
            $sheet->setCellValue("K{$row}", $this->formatMinutes($totalEarlyMinutes));
            $sheet->setCellValue("L{$row}", $lateCount);
            $sheet->setCellValue("M{$row}", $this->formatMinutes($totalLateMinutes));
            $sheet->setCellValue("N{$row}", $permitCount);
            $sheet->setCellValue("O{$row}", $sickCount);
            $sheet->setCellValue("P{$row}", $alphaCount);
            $sheet->setCellValue("Q{$row}", "{$attendancePercentage}%");

            // Zebra striping
            if ($no % 2 === 0) {
                $sheet->getStyle("A{$row}:Q{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            // Alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:Q{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("A{$row}:Q{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet->getRowDimension($row)->setRowHeight(20);

            $row++;
            $no++;
        }

        // Total Row Summary
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("F{$row}", "=SUM(F6:F{$lastDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G6:G{$lastDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H6:H{$lastDataRow})");
            $sheet->setCellValue("I{$row}", "=SUM(I6:I{$lastDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J6:J{$lastDataRow})");
            $sheet->setCellValue("K{$row}", "-");
            $sheet->setCellValue("L{$row}", "=SUM(L6:L{$lastDataRow})");
            $sheet->setCellValue("M{$row}", "-");
            $sheet->setCellValue("N{$row}", "=SUM(N6:N{$lastDataRow})");
            $sheet->setCellValue("O{$row}", "=SUM(O6:O{$lastDataRow})");
            $sheet->setCellValue("P{$row}", "=SUM(P6:P{$lastDataRow})");
            $sheet->setCellValue("Q{$row}", "-");

            $totalStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E1B4B']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E7FF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4F46E5']],
                ],
            ];
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray($totalStyle);
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // Auto size columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekap_Absensi_Pegawai_' . Carbon::parse($startDate)->format('Ymd') . '_' . Carbon::parse($endDate)->format('Ymd') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
