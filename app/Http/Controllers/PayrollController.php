<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\Unit;
use App\Models\User;
use App\Services\PayrollCalculationService;
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

class PayrollController extends Controller
{
    protected PayrollCalculationService $calculationService;

    public function __construct(PayrollCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Tampilkan halaman utama penggajian guru & filter periode bulanan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuru = !$user->isSuperAdmin() && $user->hasRole('user');

        $periodMonth = $request->get('period', Carbon::now()->format('Y-m'));
        $statusFilter = $request->get('status');
        $unitFilter = $request->get('unit_id');
        $search = $request->get('search');

        $query = Payroll::with(['user.units', 'details.unit', 'adjustments'])
            ->where('period_month', $periodMonth)
            ->orderBy('net_salary', 'desc');

        // Jika user adalah guru biasa, hanya bisa melihat slip gajinya sendiri
        if ($isGuru) {
            $query->where('user_id', $user->id);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($unitFilter) {
            $query->whereHas('user.units', function ($q) use ($unitFilter) {
                $q->where('units.id', $unitFilter);
            });
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->paginate(20)->withQueryString();
        $units = Unit::where('is_active', true)->orderBy('id', 'asc')->get();

        // Rekap KPI Penggajian Bulan Terpilih
        $kpiQuery = Payroll::where('period_month', $periodMonth);
        if ($isGuru) {
            $kpiQuery->where('user_id', $user->id);
        }

        $totalGross = (float)$kpiQuery->sum('gross_teaching_amount');
        $totalAllowances = (float)$kpiQuery->sum('total_allowances');
        $totalDeductions = (float)$kpiQuery->sum('total_deductions');
        $totalNet = (float)$kpiQuery->sum('net_salary');
        $totalHours = (float)$kpiQuery->sum('total_hours_taught');
        $totalTeachers = $kpiQuery->count();
        $totalPaid = (clone $kpiQuery)->where('status', 'paid')->count();

        $allTeachers = User::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('payrolls.index', compact(
            'payrolls',
            'units',
            'periodMonth',
            'statusFilter',
            'unitFilter',
            'search',
            'totalGross',
            'totalAllowances',
            'totalDeductions',
            'totalNet',
            'totalHours',
            'totalTeachers',
            'totalPaid',
            'allTeachers',
            'isGuru'
        ));
    }

    /**
     * Hitung gaji otomatis (Generate Payroll) untuk periode tertentu
     */
    public function generate(Request $request)
    {
        $request->validate([
            'period_month' => 'required|date_format:Y-m',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'period_month.required' => 'Periode bulan wajib dipilih.',
            'period_month.date_format' => 'Format periode bulan tidak valid (YYYY-MM).',
        ]);

        $periodMonth = $request->period_month;
        $userId = $request->user_id;
        $adminId = Auth::id();

        if ($userId) {
            // Kalkulasi perorangan guru
            $targetUser = User::findOrFail($userId);
            $payroll = $this->calculationService->calculateForUser($targetUser, $periodMonth, $adminId, true);
            return redirect()->route('payrolls.index', ['period' => $periodMonth])
                ->with('success', "Gaji untuk guru {$targetUser->name} pada periode {$payroll->formatted_period} berhasil dihitung ulang.");
        }

        // Kalkulasi seluruh guru aktif
        $result = $this->calculationService->calculateAll($periodMonth, $adminId);

        return redirect()->route('payrolls.index', ['period' => $periodMonth])
            ->with('success', "Kalkulasi otomatis selesai! {$result['total_users']} guru berhasil diproses dengan total Take Home Pay Rp " . number_format($result['total_net'], 0, ',', '.') . ".");
    }

    /**
     * Tampilkan detail rincian breakdown gaji seorang guru
     */
    public function show(Payroll $payroll)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $user->hasRole('user') && $payroll->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat slip gaji pegawai lain.');
        }

        $payroll->load(['user.units', 'details.unit', 'adjustments', 'processor']);

        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Tambah penyesuaian (tunjangan atau potongan) ke slip gaji
     */
    public function storeAdjustment(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'Tidak dapat mengubah gaji yang sudah berstatus Dibayar (Paid).');
        }

        $request->validate([
            'type' => 'required|in:allowance,deduction',
            'name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
        ], [
            'type.required' => 'Jenis penyesuaian wajib dipilih.',
            'name.required' => 'Nama tunjangan / potongan wajib diisi.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka positif.',
        ]);

        PayrollAdjustment::create([
            'payroll_id' => $payroll->id,
            'type' => $request->type,
            'name' => trim($request->name),
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $payroll->recalculateTotals();

        return redirect()->route('payrolls.show', $payroll->id)
            ->with('success', "Penyesuaian '{$request->name}' berhasil ditambahkan ke rincian gaji.");
    }

    /**
     * Hapus penyesuaian dari slip gaji
     */
    public function destroyAdjustment(Payroll $payroll, PayrollAdjustment $adjustment)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'Tidak dapat mengubah gaji yang sudah berstatus Dibayar (Paid).');
        }

        if ($adjustment->payroll_id !== $payroll->id) {
            abort(404);
        }

        $adjustmentName = $adjustment->name;
        $adjustment->delete();

        $payroll->recalculateTotals();

        return redirect()->route('payrolls.show', $payroll->id)
            ->with('success', "Komponen penyesuaian '{$adjustmentName}' berhasil dihapus.");
    }

    /**
     * Perbarui status penggajian (Draft -> Locked -> Paid)
     */
    public function updateStatus(Request $request, Payroll $payroll)
    {
        $request->validate([
            'status' => 'required|in:draft,locked,paid',
        ]);

        $newStatus = $request->status;
        $payroll->status = $newStatus;

        if ($newStatus === 'paid' && !$payroll->paid_at) {
            $payroll->paid_at = now();
        }

        $payroll->save();

        return redirect()->back()->with('success', "Status penggajian berhasil diperbarui menjadi " . strtoupper($newStatus) . ".");
    }

    /**
     * Hitung ulang payroll satu orang guru
     */
    public function recalculate(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'Gaji yang sudah berstatus Paid tidak dapat dihitung ulang.');
        }

        $this->calculationService->calculateForUser($payroll->user, $payroll->period_month, Auth::id(), true);

        return redirect()->route('payrolls.show', $payroll->id)
            ->with('success', 'Rincian jam mengajar dan honor berhasil disinkronkan ulang dengan presensi terbaru.');
    }

    /**
     * Hapus draft payroll
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus gaji yang sudah berstatus Paid.');
        }

        $period = $payroll->period_month;
        $payroll->delete();

        return redirect()->route('payrolls.index', ['period' => $period])
            ->with('success', 'Data draft payroll berhasil dihapus.');
    }

    /**
     * Cetak Slip Gaji Resmi Guru (Single PDF A4/A5)
     */
    public function exportPdf(Payroll $payroll)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $user->hasRole('user') && $payroll->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh slip gaji ini.');
        }

        $payroll->load(['user.units', 'details.unit', 'adjustments']);

        $pdf = Pdf::loadView('payrolls.slip-pdf', compact('payroll'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $fileName = 'Slip_Gaji_' . str_replace(' ', '_', $payroll->user->name) . '_' . $payroll->period_month . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Cetak Laporan Rekapitulasi Gaji Yayasan ke PDF (Landscape A4)
     */
    public function exportSummaryPdf(Request $request)
    {
        $periodMonth = $request->get('period', Carbon::now()->format('Y-m'));
        $unitFilter = $request->get('unit_id');

        $query = Payroll::with(['user.units', 'details.unit', 'adjustments'])
            ->where('period_month', $periodMonth)
            ->orderBy('net_salary', 'desc');

        if ($unitFilter) {
            $query->whereHas('user.units', function ($q) use ($unitFilter) {
                $q->where('units.id', $unitFilter);
            });
        }

        $payrolls = $query->get();
        $selectedUnit = $unitFilter ? Unit::find($unitFilter) : null;
        $formattedPeriod = Carbon::createFromFormat('Y-m', $periodMonth)->translatedFormat('F Y');

        $pdf = Pdf::loadView('payrolls.pdf-summary', compact('payrolls', 'periodMonth', 'formattedPeriod', 'selectedUnit'))
            ->setPaper('a4', 'landscape')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $fileName = 'Rekap_Penggajian_Yayasan_' . $periodMonth . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Ekspor Rekapitulasi Penggajian Yayasan ke Spreadsheet Excel (.xlsx)
     */
    public function exportSummaryExcel(Request $request): StreamedResponse
    {
        $periodMonth = $request->get('period', Carbon::now()->format('Y-m'));
        $unitFilter = $request->get('unit_id');

        $query = Payroll::with(['user.units', 'details.unit', 'adjustments'])
            ->where('period_month', $periodMonth)
            ->orderBy('net_salary', 'desc');

        if ($unitFilter) {
            $query->whereHas('user.units', function ($q) use ($unitFilter) {
                $q->where('units.id', $unitFilter);
            });
        }

        $payrolls = $query->get();
        $formattedPeriod = Carbon::createFromFormat('Y-m', $periodMonth)->translatedFormat('F Y');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Gaji ' . substr($periodMonth, 5));

        // Header Dokumen
        $sheet->setCellValue('A1', 'REKAPITULASI PENGGAJIAN & HONOR MENGAJAR GURU');
        $sheet->setCellValue('A2', 'PERIODE: ' . strtoupper($formattedPeriod));
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Kolom Tabel
        $headers = [
            'A4' => 'NO',
            'B4' => 'NIP / ID',
            'C4' => 'NAMA GURU',
            'D4' => 'JENJANG / UNIT',
            'E4' => 'HARI HADIR',
            'F4' => 'TOTAL JAM (JAM)',
            'G4' => 'HONOR MENGAJAR (RP)',
            'H4' => 'TUNJANGAN (RP)',
            'I4' => 'POTONGAN (RP)',
            'J4' => 'TAKE HOME PAY (RP)',
            'K4' => 'STATUS',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A4:K4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Data Rows
        $row = 5;
        $no = 1;
        foreach ($payrolls as $p) {
            $units = $p->user->units->pluck('name')->implode(', ') ?: 'Yayasan';

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $p->user->nip ?: '-');
            $sheet->setCellValue("C{$row}", $p->user->name);
            $sheet->setCellValue("D{$row}", $units);
            $sheet->setCellValue("E{$row}", $p->total_present_days);
            $sheet->setCellValue("F{$row}", (float)$p->total_hours_taught);
            $sheet->setCellValue("G{$row}", (float)$p->gross_teaching_amount);
            $sheet->setCellValue("H{$row}", (float)$p->total_allowances);
            $sheet->setCellValue("I{$row}", (float)$p->total_deductions);
            $sheet->setCellValue("J{$row}", (float)$p->net_salary);
            $sheet->setCellValue("K{$row}", strtoupper($p->status));

            // Format number
            $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Summary Row
        $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("F{$row}", "=SUM(F5:F" . ($row - 1) . ")");
        $sheet->setCellValue("G{$row}", "=SUM(G5:G" . ($row - 1) . ")");
        $sheet->setCellValue("H{$row}", "=SUM(H5:H" . ($row - 1) . ")");
        $sheet->setCellValue("I{$row}", "=SUM(I5:I" . ($row - 1) . ")");
        $sheet->setCellValue("J{$row}", "=SUM(J5:J" . ($row - 1) . ")");

        $summaryStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E7FF'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle("A{$row}:K{$row}")->applyFromArray($summaryStyle);
        $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');

        // Auto-fit columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Rekap_Penggajian_Yayasan_' . $periodMonth . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
