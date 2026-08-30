<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Ambang batas default jam masuk tepat waktu
     */
    protected string $defaultOnTimeThreshold = '07:30:00';

    /**
     * Konversi waktu HH:MM:SS ke integer menit
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
     * Format menit ke string jam & menit (contoh: 2j 15m atau 45m)
     */
    protected function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }
        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;
        if ($hours > 0) {
            return "{$hours}j {$mins}m";
        }
        return "{$mins}m";
    }

    /**
     * Halaman Utama Laporan Presensi & Analitik Kehadiran
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $canManageAll = $isSuperAdmin || $currentUser->canAccessMenu('reports', 'view') || $currentUser->canAccessMenu('attendances', 'view');

        // 1. Penentuan Filter Periode (Bulan & Tahun atau Rentang Tanggal)
        $selectedMonth = (int)$request->input('month', now()->month);
        $selectedYear  = (int)$request->input('year', now()->year);

        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();

        // 2. Query Data Presensi Periode Ini
        $attendanceQuery = Attendance::with(['user.units', 'user.role'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        // Jika bukan superadmin / admin yang bisa lihat semua, filter hanya data miliknya
        if (!$canManageAll) {
            $attendanceQuery->where('user_id', $currentUser->id);
        }

        // Filter Unit / Jenjang Sekolah jika ada
        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $attendanceQuery->whereHas('user.units', fn($uq) => $uq->where('units.id', $unitId));
        }

        // Filter Status Kehadiran
        if ($request->filled('status')) {
            $attendanceQuery->where('status', $request->input('status'));
        }

        // Filter Pencarian Pegawai
        if ($request->filled('search')) {
            $search = $request->input('search');
            $attendanceQuery->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Ambil semua records untuk kalkulasi KPI & Chart
        $allRecords = (clone $attendanceQuery)->get();

        // 3. Kalkulasi 6 Metrik KPI Presensi
        $totalLogs     = $allRecords->count();
        $hadirCount    = $allRecords->where('status', 'hadir')->count();
        $lateCount     = $allRecords->where('status', 'terlambat')->count();
        $izinCount     = $allRecords->where('status', 'izin')->count();
        $sakitCount    = $allRecords->where('status', 'sakit')->count();
        $alpaCount     = $allRecords->where('status', 'alpa')->count();

        $presenceCount = $hadirCount + $lateCount;
        $attendanceRate = $totalLogs > 0 ? round(($presenceCount / $totalLogs) * 100, 1) : 0;

        // Total Keterlambatan dalam Menit
        $totalLateMinutes = 0;
        foreach ($allRecords as $rec) {
            if ($rec->check_in) {
                $threshold = $this->timeToMinutes($this->defaultOnTimeThreshold);
                if ($rec->user) {
                    $sch = $rec->user->getWorkScheduleForDate($rec->attendance_date);
                    if ($sch->time_in) {
                        $threshold = $this->timeToMinutes($sch->time_in);
                    }
                }
                $inMin = $this->timeToMinutes($rec->check_in);
                if ($inMin && $inMin > $threshold) {
                    $totalLateMinutes += ($inMin - $threshold);
                }
            }
        }

        $kpi = [
            'total_logs'         => $totalLogs,
            'hadir_count'        => $hadirCount,
            'late_count'         => $lateCount,
            'izin_count'         => $izinCount,
            'sakit_count'        => $sakitCount,
            'alpa_count'         => $alpaCount,
            'attendance_rate'    => $attendanceRate,
            'total_late_minutes' => $totalLateMinutes,
            'total_late_formatted' => $this->formatMinutes($totalLateMinutes),
            'total_employees'    => User::where('is_active', true)->count(),
        ];

        // 4. Data Grafik Tren Kehadiran Harian (Berdasarkan tanggal presensi di bulan ini)
        $distinctDates = $allRecords->pluck('attendance_date')
            ->unique()
            ->sort()
            ->values();

        $chartLabels    = [];
        $chartHadir     = [];
        $chartTerlambat = [];
        $chartIzinSakit = [];
        $chartAlpa      = [];

        foreach ($distinctDates as $d) {
            $dateStr   = Carbon::parse($d)->format('Y-m-d');
            $dateLabel = Carbon::parse($d)->translatedFormat('d M');

            $dayGroup = $allRecords->where('attendance_date', $dateStr);
            $chartLabels[]    = $dateLabel;
            $chartHadir[]     = $dayGroup->where('status', 'hadir')->count();
            $chartTerlambat[] = $dayGroup->where('status', 'terlambat')->count();
            $chartIzinSakit[] = $dayGroup->whereIn('status', ['izin', 'sakit'])->count();
            $chartAlpa[]      = $dayGroup->where('status', 'alpa')->count();
        }

        $trendChart = [
            'labels'     => $chartLabels,
            'hadir'      => $chartHadir,
            'terlambat'  => $chartTerlambat,
            'izin_sakit' => $chartIzinSakit,
            'alpa'       => $chartAlpa,
        ];

        // 5. Data Diagram Komposisi Status
        $distributionChart = [
            'labels' => ['Hadir Tepat', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            'data'   => [$hadirCount, $lateCount, $izinCount, $sakitCount, $alpaCount],
        ];

        // 6. Tabel Data Laporan (Paginated)
        $attendances = $attendanceQuery->orderBy('attendance_date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 7. Master Unit Sekolah untuk filter
        $units = Unit::where('is_active', true)->get();

        $periodLabel = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y');

        return view('reports.index', compact(
            'attendances',
            'kpi',
            'trendChart',
            'distributionChart',
            'units',
            'selectedMonth',
            'selectedYear',
            'periodLabel',
            'canManageAll'
        ));
    }

    /**
     * Ekspor Laporan Presensi ke File Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $canManageAll = $isSuperAdmin || $currentUser->canAccessMenu('reports', 'export') || $currentUser->canAccessMenu('attendances', 'export');

        $selectedMonth = (int)$request->input('month', now()->month);
        $selectedYear  = (int)$request->input('year', now()->year);

        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();

        $query = Attendance::with(['user.units', 'user.role'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if (!$canManageAll) {
            $query->where('user_id', $currentUser->id);
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->whereHas('user.units', fn($uq) => $uq->where('units.id', $unitId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('attendance_date', 'asc')->orderBy('check_in', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Presensi');

        // Header Judul
        $periodText = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y');
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PRESENSI & KEHADIRAN PEGAWAI');
        $sheet->setCellValue('A2', "Periode: {$periodText} (01/{$selectedMonth}/{$selectedYear} s.d. " . Carbon::parse($endDate)->format('d/m/Y') . ')');
        $sheet->setCellValue('A3', 'Diekspor pada: ' . now()->translatedFormat('l, d F Y H:i') . ' WIB • Dicetak oleh: ' . $currentUser->name);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E1B4B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'));

        // Header Kolom Tabel
        $headers = [
            'A5' => 'NO',
            'B5' => 'NIP',
            'C5' => 'NAMA PEGAWAI / GURU',
            'D5' => 'UNIT / JENJANG',
            'E5' => 'TANGGAL',
            'F5' => 'HARI',
            'G5' => 'SCAN MASUK',
            'H5' => 'SCAN PULANG',
            'I5' => 'STATUS',
            'J5' => 'TERLAMBAT (MENIT)',
            'K5' => 'KETERANGAN',
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
        $sheet->getStyle('A5:K5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Isi Data
        $row = 6;
        $no  = 1;

        $totalHadir = 0;
        $totalLate  = 0;
        $totalIzin  = 0;
        $totalSakit = 0;
        $totalAlpa  = 0;

        foreach ($records as $att) {
            $user = $att->user;
            $unitStr = $user && $user->units->isNotEmpty() ? $user->units->pluck('name')->implode(', ') : '-';
            $tgl = $att->attendance_date ? Carbon::parse($att->attendance_date) : null;

            // Hitung keterlambatan menit
            $lateMin = 0;
            if ($att->check_in) {
                $threshold = $this->timeToMinutes($this->defaultOnTimeThreshold);
                if ($user) {
                    $sch = $user->getWorkScheduleForDate($att->attendance_date);
                    if ($sch->time_in) {
                        $threshold = $this->timeToMinutes($sch->time_in);
                    }
                }
                $inMin = $this->timeToMinutes($att->check_in);
                if ($inMin && $inMin > $threshold) {
                    $lateMin = $inMin - $threshold;
                }
            }

            // Counter status
            if ($att->status === 'hadir') $totalHadir++;
            elseif ($att->status === 'terlambat') $totalLate++;
            elseif ($att->status === 'izin') $totalIzin++;
            elseif ($att->status === 'sakit') $totalSakit++;
            elseif ($att->status === 'alpa') $totalAlpa++;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValueExplicit("B{$row}", $user?->nip ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $user?->name ?? 'Pegawai Dihapus');
            $sheet->setCellValue("D{$row}", $unitStr);
            $sheet->setCellValue("E{$row}", $tgl ? $tgl->format('Y-m-d') : '-');
            $sheet->setCellValue("F{$row}", $tgl ? $tgl->translatedFormat('l') : '-');
            $sheet->setCellValue("G{$row}", $att->check_in ? substr($att->check_in, 0, 5) : '-');
            $sheet->setCellValue("H{$row}", $att->check_out ? substr($att->check_out, 0, 5) : '-');
            $sheet->setCellValue("I{$row}", strtoupper($att->status));
            $sheet->setCellValue("J{$row}", $lateMin > 0 ? $lateMin . ' m' : '-');
            $sheet->setCellValue("K{$row}", $att->notes ?: '-');

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:K{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet->getRowDimension($row)->setRowHeight(20);

            $row++;
            $no++;
        }

        // Summary Baris
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->setCellValue("A{$row}", 'RINGKASAN TOTAL: ' . ($no - 1) . " Record | Hadir: {$totalHadir} | Terlambat: {$totalLate} | Izin: {$totalIzin} | Sakit: {$totalSakit} | Alpa: {$totalAlpa}");
            $sheet->mergeCells("A{$row}:K{$row}");
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '1E1B4B']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4F46E5']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(24);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Laporan_Presensi_' . $selectedYear . sprintf('%02d', $selectedMonth) . '_' . date('His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Ekspor Laporan Presensi ke File PDF (.pdf) format A4 Landscape
     */
    public function exportPdf(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $canManageAll = $isSuperAdmin || $currentUser->canAccessMenu('reports', 'export') || $currentUser->canAccessMenu('attendances', 'export');

        $selectedMonth = (int)$request->input('month', now()->month);
        $selectedYear  = (int)$request->input('year', now()->year);

        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();

        $query = Attendance::with(['user.units', 'user.role'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if (!$canManageAll) {
            $query->where('user_id', $currentUser->id);
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->whereHas('user.units', fn($uq) => $uq->where('units.id', $unitId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('attendance_date', 'asc')->orderBy('check_in', 'asc')->get();

        $totalLogs  = $records->count();
        $hadirCount = $records->where('status', 'hadir')->count();
        $lateCount  = $records->where('status', 'terlambat')->count();
        $izinCount  = $records->where('status', 'izin')->count();
        $sakitCount = $records->where('status', 'sakit')->count();
        $alpaCount  = $records->where('status', 'alpa')->count();
        $presenceCount = $hadirCount + $lateCount;
        $attendanceRate = $totalLogs > 0 ? round(($presenceCount / $totalLogs) * 100, 1) : 0;

        $kpi = [
            'total_logs'      => $totalLogs,
            'hadir_count'     => $hadirCount,
            'late_count'      => $lateCount,
            'izin_count'      => $izinCount,
            'sakit_count'     => $sakitCount,
            'alpa_count'      => $alpaCount,
            'attendance_rate' => $attendanceRate,
        ];

        $periodLabel = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('reports.pdf', compact(
            'records',
            'kpi',
            'periodLabel',
            'selectedMonth',
            'selectedYear',
            'currentUser'
        ))
        ->setPaper('a4', 'landscape')
        ->setOption([
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);

        $fileName = 'Laporan_Presensi_' . $selectedYear . sprintf('%02d', $selectedMonth) . '_' . date('His') . '.pdf';
        return $pdf->download($fileName);
    }
}
