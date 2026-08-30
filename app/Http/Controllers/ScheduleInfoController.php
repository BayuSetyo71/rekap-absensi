<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTeachingSlot;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleInfoController extends Controller
{
    /**
     * Daftar nama hari Indonesia (1 = Senin s.d. 7 = Minggu)
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
     * Menampilkan Halaman Informasi Jadwal Mengajar Guru Yayasan
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $todayNum = (int)Carbon::now()->dayOfWeekIso; // 1 = Senin s.d. 7 = Minggu
        $selectedDay = (int)$request->input('day', $todayNum);
        if ($selectedDay < 1 || $selectedDay > 7) {
            $selectedDay = $todayNum;
        }

        // 1. Data Master Unit Sekolah
        $units = Unit::with('schedules')->where('is_active', true)->get();

        // 2. Query Sesi Mengajar Hari Ini (atau Hari yang Dipilih)
        $slotsQuery = EmployeeTeachingSlot::with(['user', 'unit'])
            ->whereHas('user', fn($uq) => $uq->where('is_active', true))
            ->where('day_of_week', $selectedDay);

        // Filter Unit pada Sesi
        if ($request->filled('unit_id')) {
            $slotsQuery->where('unit_id', $request->input('unit_id'));
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->input('search');
            $slotsQuery->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                  });
            });
        }

        // Ambil data sesi terurut berdasarkan jam mulai
        $dailySlots = $slotsQuery->orderBy('start_time', 'asc')->get();

        // Kelompokkan sesi hari ini berdasarkan Unit Sekolah (TK, SD, SMP, SMA, Yayasan)
        $slotsByUnit = [];
        foreach ($units as $unit) {
            $slotsByUnit[$unit->code] = [
                'unit'  => $unit,
                'slots' => $dailySlots->where('unit_id', $unit->id),
            ];
        }

        // 3. Query Daftar Guru & Matriks Jadwal Mingguan (Senin - Minggu)
        $teachersQuery = User::with([
            'units',
            'teachingSlots.unit',
            'workSchedules.unit',
        ])->where('is_active', true);

        // Jika role guru biasa dan bukan admin, hanya bisa melihat dirinya jika tidak ada hak kelola
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('schedule-info', 'create') && !$currentUser->canAccessMenu('users', 'view')) {
            // Tetap izinkan melihat jadwal seluruh guru untuk transparansi jadwal mengajar yayasan
        }

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $teachersQuery->where(function ($q) use ($unitId) {
                $q->whereHas('units', fn($uq) => $uq->where('units.id', $unitId))
                  ->orWhereHas('teachingSlots', fn($tq) => $tq->where('unit_id', $unitId));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $teachersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('teachingSlots', fn($tq) => $tq->where('subject', 'like', "%{$search}%"));
            });
        }

        $allTeachers = (clone $teachersQuery)->get();
        $teachers = $teachersQuery->orderBy('name', 'asc')->paginate(15)->withQueryString();

        // 4. Analisis Statistik KPI Ringkasan
        $todayTeachersCount = $dailySlots->pluck('user_id')->unique()->count();
        $todayTotalMinutes = $dailySlots->sum(fn($s) => $s->duration_minutes);
        $todayHoursFormatted = round($todayTotalMinutes / 60, 1);

        $stats = [
            'today_day_name'         => $this->dayNames[$selectedDay] ?? 'Hari Ini',
            'selected_day'           => $selectedDay,
            'today_teachers_count'   => $todayTeachersCount,
            'today_slots_count'      => $dailySlots->count(),
            'today_hours'            => $todayHoursFormatted,
            'total_active_teachers'  => $allTeachers->count(),
            'total_configured'       => $allTeachers->filter(fn($u) => $u->hasConfiguredSchedule())->count(),
        ];

        // 5. Analisis Beban Jam Mengajar Mingguan Seluruh Guru
        $workloadAnalysis = $allTeachers->map(function ($teacher) {
            $totalMins = $teacher->teachingSlots->sum(fn($s) => $s->duration_minutes);
            $totalSlots = $teacher->teachingSlots->count();
            $unitsTaught = $teacher->teachingSlots->pluck('unit.code')->filter()->unique()->values();

            return [
                'id'            => $teacher->id,
                'name'          => $teacher->name,
                'nip'           => $teacher->nip ?: '-',
                'avatar_url'    => $teacher->avatar_url,
                'position'      => $teacher->position ?: 'Guru',
                'total_minutes' => $totalMins,
                'total_hours'   => round($totalMins / 60, 1),
                'total_slots'   => $totalSlots,
                'units_taught'  => $unitsTaught,
                'is_configured' => $teacher->hasConfiguredSchedule(),
            ];
        })->sortByDesc('total_minutes')->values();

        return view('schedule-info.index', compact(
            'units',
            'dailySlots',
            'slotsByUnit',
            'teachers',
            'stats',
            'workloadAnalysis',
            'selectedDay'
        ));
    }

    /**
     * Ekspor Laporan Informasi Jadwal Mengajar Guru ke File Excel
     */
    public function exportExcel(Request $request)
    {
        $query = User::with(['role', 'units', 'workSchedules.unit', 'teachingSlots.unit'])->where('is_active', true);

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->where(function ($q) use ($unitId) {
                $q->whereHas('units', fn($uq) => $uq->where('units.id', $unitId))
                  ->orWhereHas('teachingSlots', fn($tq) => $tq->where('unit_id', $unitId));
            });
        }

        $teachers = $query->orderBy('name', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Mengajar Guru');

        // Judul Laporan
        $sheet->setCellValue('A1', 'LAPORAN INFORMASI JADWAL MENGAJAR GURU YAYASAN');
        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y, H:i') . ' WIB • TK, SD, SMP, SMA, Yayasan');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7280');

        // Header Tabel
        $headers = [
            'A4' => 'NO',
            'B4' => 'NIP',
            'C4' => 'NAMA GURU / PEGAWAI',
            'D4' => 'JABATAN',
            'E4' => 'JENJANG DIAMPU',
            'F4' => 'SENIN',
            'G4' => 'SELASA',
            'H4' => 'RABU',
            'I4' => 'KAMIS',
            'J4' => 'JUMAT',
            'K4' => 'SABTU',
            'L4' => 'MINGGU',
            'M4' => 'TOTAL SESI',
            'N4' => 'TOTAL JAM',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']],
            ],
        ];
        $sheet->getStyle('A4:N4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Isi Baris Data
        $row = 5;
        $dayCols = [1 => 'F', 2 => 'G', 3 => 'H', 4 => 'I', 5 => 'J', 6 => 'K', 7 => 'L'];

        foreach ($teachers as $idx => $t) {
            $unitsStr = $t->units->pluck('name')->implode(', ') ?: ($t->hasConfiguredSchedule() ? 'Umum' : 'Belum Diatur');
            $totalMins = $t->teachingSlots->sum(fn($s) => $s->duration_minutes);
            $totalHours = round($totalMins / 60, 1);
            $totalSlots = $t->teachingSlots->count();

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $t->nip ?: '-');
            $sheet->setCellValue("C{$row}", $t->name);
            $sheet->setCellValue("D{$row}", $t->position ?: 'Guru');
            $sheet->setCellValue("E{$row}", $unitsStr);

            foreach ($dayCols as $dayNum => $col) {
                $sch = $t->getWorkScheduleForDay($dayNum);

                if (!$t->hasConfiguredSchedule()) {
                    $cellVal = '-';
                } elseif ($sch->is_day_off) {
                    $cellVal = 'LIBUR';
                } elseif ($sch->slots && $sch->slots->isNotEmpty()) {
                    $slotTexts = [];
                    foreach ($sch->slots as $s) {
                        $uCode = $s->unit ? $s->unit->code : 'Unit';
                        $sub = $s->subject ? " ({$s->subject})" : '';
                        $slotTexts[] = "• {$uCode}: {$s->formatted_start_time}-{$s->formatted_end_time}{$sub}";
                    }
                    $cellVal = implode("\n", $slotTexts);
                } elseif ($sch->time_in && $sch->time_out) {
                    $in = substr($sch->time_in, 0, 5);
                    $out = substr($sch->time_out, 0, 5);
                    $uCode = $sch->unit ? " ({$sch->unit->code})" : '';
                    $cellVal = "{$in} - {$out}{$uCode}";
                } else {
                    $cellVal = 'LIBUR';
                }

                $sheet->setCellValue("{$col}{$row}", $cellVal);
            }

            $sheet->setCellValue("M{$row}", $totalSlots . ' Sesi');
            $sheet->setCellValue("N{$row}", $totalHours . ' Jam');

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
            $sheet->getStyle("M{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

            $row++;
        }

        foreach (range('A', 'N') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Informasi_Jadwal_Mengajar_' . date('Y-m-d_His') . '.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Ekspor Laporan Informasi Jadwal Seluruh Guru Yayasan ke PDF (Format Landscape A4)
     */
    public function exportPdf(Request $request)
    {
        $query = User::with(['role', 'units', 'workSchedules.unit', 'teachingSlots.unit'])->where('is_active', true);

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->where(function ($q) use ($unitId) {
                $q->whereHas('units', fn($uq) => $uq->where('units.id', $unitId))
                  ->orWhereHas('teachingSlots', fn($tq) => $tq->where('unit_id', $unitId));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name', 'asc')->get();

        $pdf = Pdf::loadView('schedule-info.pdf-all', compact('teachers'))
            ->setPaper('a4', 'landscape')
            ->setOption([
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        $fileName = 'Laporan_Jadwal_Guru_Yayasan_' . date('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Ekspor Jadwal Mengajar Khusus Pegawai Tertentu ke PDF (Format Portrait A4)
     */
    public function exportPersonalPdf(Request $request, User $user)
    {
        $teacher = $user->load(['role', 'units', 'workSchedules.unit', 'teachingSlots.unit']);

        $pdf = Pdf::loadView('schedule-info.pdf-personal', compact('teacher'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $teacher->name);
        $fileName = "Jadwal_Mengajar_{$safeName}.pdf";

        return $pdf->download($fileName);
    }
}
