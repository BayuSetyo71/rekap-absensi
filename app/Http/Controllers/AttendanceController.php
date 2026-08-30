<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Menampilkan daftar riwayat absensi dengan filter dan ringkasan statistik
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with(['user', 'creator']);

        // Jika user biasa tanpa hak akses penuh, hanya tampilkan data miliknya
        if (!$user->isSuperAdmin() && !$user->canAccessMenu('users', 'view')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter rentang tanggal (default 30 hari terakhir jika kosong)
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        // Filter status kehadiran
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->input('status')));
        }

        // Filter pencarian nama / NIP karyawan
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Hitung statistik berdasarkan filter
        $statsQuery = clone $query;
        $allFiltered = $statsQuery->get();

        $stats = [
            'total'     => $allFiltered->count(),
            'hadir'     => $allFiltered->where('status', 'hadir')->count(),
            'terlambat' => $allFiltered->where('status', 'terlambat')->count(),
            'izin'      => $allFiltered->where('status', 'izin')->count(),
            'sakit'     => $allFiltered->where('status', 'sakit')->count(),
            'alpa'      => $allFiltered->where('status', 'alpa')->count(),
        ];

        $attendances = $query->orderBy('attendance_date', 'desc')
                             ->orderBy('check_in', 'desc')
                             ->paginate(15)
                             ->withQueryString();

        $employees = User::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('attendances.index', compact('attendances', 'stats', 'employees', 'startDate', 'endDate'));
    }

    /**
     * Menyimpan data absensi manual (single entry)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable'],
            'check_out' => ['nullable'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpa'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'user_id.required' => 'Pegawai wajib dipilih.',
            'attendance_date.required' => 'Tanggal absensi wajib diisi.',
            'status.required' => 'Status kehadiran wajib dipilih.',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $request->input('user_id'),
                'attendance_date' => $request->input('attendance_date'),
            ],
            [
                'check_in' => $request->filled('check_in') ? $this->parseTimeValue($request->input('check_in')) : null,
                'check_out' => $request->filled('check_out') ? $this->parseTimeValue($request->input('check_out')) : null,
                'status' => $request->input('status'),
                'notes' => $request->input('notes'),
                'created_by' => Auth::id(),
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil disimpan.',
                'data' => $attendance,
            ]);
        }

        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil disimpan.');
    }

    /**
     * Mengambil detail absensi untuk modal edit (JSON via jQuery AJAX)
     */
    public function edit(Attendance $attendance)
    {
        return response()->json([
            'status' => 'success',
            'data' => $attendance->load('user'),
        ]);
    }

    /**
     * Memperbarui data absensi
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'check_in' => ['nullable'],
            'check_out' => ['nullable'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpa'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'status.required' => 'Status kehadiran wajib dipilih.',
        ]);

        $attendance->update([
            'check_in' => $request->filled('check_in') ? $this->parseTimeValue($request->input('check_in')) : null,
            'check_out' => $request->filled('check_out') ? $this->parseTimeValue($request->input('check_out')) : null,
            'status' => $request->input('status'),
            'notes' => $request->input('notes'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil diperbarui.',
                'data' => $attendance,
            ]);
        }

        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Menghapus catatan absensi
     */
    public function destroy(Request $request, Attendance $attendance)
    {
        $attendance->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil dihapus.',
            ]);
        }

        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Mengunduh file template Excel (.xlsx) untuk inject data
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Absensi');

        // Header Styling
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
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
        ];

        $headers = [
            'A1' => 'ID',
            'B1' => 'NAMA',
            'C1' => 'TANGGAL',
            'D1' => 'C-IN',
            'E1' => 'C-OUT',
            'F1' => 'STATUS',
            'G1' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Ambil data beberapa pegawai untuk contoh nyata
        $sampleEmployees = User::where('is_active', true)->take(3)->get();
        $row = 2;

        if ($sampleEmployees->isNotEmpty()) {
            foreach ($sampleEmployees as $idx => $emp) {
                $statusSample = match ($idx) {
                    0 => 'hadir',
                    1 => 'terlambat',
                    2 => 'izin',
                    default => 'hadir',
                };
                $inSample = ($statusSample === 'izin') ? '' : '07:25:00';
                $outSample = ($statusSample === 'izin') ? '' : '11:30:00';

                $sheet->setCellValue("A{$row}", $emp->nip ?: $emp->id);
                $sheet->setCellValue("B{$row}", $emp->name);
                $sheet->setCellValue("C{$row}", now()->subDays(2 - $idx)->format('m/d/Y'));
                $sheet->setCellValue("D{$row}", $inSample);
                $sheet->setCellValue("E{$row}", $outSample);
                $sheet->setCellValue("F{$row}", $statusSample);
                $sheet->setCellValue("G{$row}", ($statusSample === 'izin' ? 'Izin Keperluan Keluarga' : 'Presensi Fingerprint'));
                $row++;
            }
        }

        // Catatan Panduan Pengisian di Bagian Bawah
        $infoRow = $row + 2;
        $sheet->setCellValue("A{$infoRow}", "PANDUAN FORMAT EXCEL FINGERPRINT / INJECT ABSENSI:");
        $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true)->getColor()->setRGB('4338CA');

        $instructions = [
            "1. Kolom ID / NIP / PIN : Berisi ID Mesin Fingerprint atau NIP pegawai terdaftar.",
            "2. Kolom NAMA           : Nama pegawai (jika pegawai belum ada, sistem dapat membuatkannya otomatis).",
            "3. Kolom TANGGAL        : Format tanggal MM/DD/YYYY atau YYYY-MM-DD (contoh: 07/27/2026 atau 2026-07-27).",
            "4. Kolom C-IN / MASUK   : Jam scan masuk (contoh: 07:26:48).",
            "5. Kolom C-OUT / PULANG : Jam scan pulang (contoh: 11:12:24).",
            "6. Kolom STATUS         : hadir, terlambat, izin, sakit, alpa (opsional, otomatis dihitung jika kosong).",
            "7. Kolom KETERANGAN     : Catatan tambahan / shift kerja.",
        ];

        foreach ($instructions as $i => $text) {
            $sheet->setCellValue('A' . ($infoRow + 1 + $i), $text);
        }

        // Auto-fit column widths
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Fingerprint_Absensi_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * STEP 1: Upload & Pratinjau (Preview) Data Excel sebelum disimpan
     */
    public function previewExcel(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'excel_file.required' => 'Silakan pilih file Excel/CSV untuk dipratinjau.',
            'excel_file.mimes' => 'Format file harus berupa .xlsx, .xls, atau .csv.',
        ]);

        $file = $request->file('excel_file');

        if (!class_exists('ZipArchive') && in_array($file->getClientOriginalExtension(), ['xlsx', 'xlsm', 'xltx', 'xltm'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ekstensi PHP "zip" belum termuat di web server Apache. Silakan lakukan Restart Apache di Laragon (Klik Stop all lalu Start all di Laragon) agar modul zip aktif.',
            ], 500);
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            
            // Prioritaskan sheet pertama atau sheet bernama 'RAW FINGERPRINT'
            $sheet = $spreadsheet->getSheetByName('RAW FINGERPRINT') ?? $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berkas Excel tidak memiliki baris data.',
                ], 422);
            }

            // Identifikasi baris header dan mapping kolom
            $headerRowIndex = 1;
            $colMap = [
                'id' => 'A',
                'name' => 'B',
                'date' => 'C',
                'check_in' => 'D',
                'check_out' => 'F',
                'status' => null,
                'notes' => 'M',
            ];

            // Scan 5 baris pertama untuk mencari baris header
            foreach (array_slice($rows, 0, 5, true) as $rIdx => $rData) {
                $rowLower = array_map(fn($v) => strtolower(trim((string)$v)), $rData);
                
                foreach ($rowLower as $cLetter => $val) {
                    if (in_array($val, ['id', 'pin', 'nip', 'nip_atau_email', 'no_id', 'id_pegawai'])) {
                        $colMap['id'] = $cLetter;
                        $headerRowIndex = $rIdx;
                    }
                    if (in_array($val, ['nama', 'name', 'nama_pegawai', 'nama karyawan'])) {
                        $colMap['name'] = $cLetter;
                    }
                    if (in_array($val, ['tanggal', 'date', 'tgl', 'attendance_date'])) {
                        $colMap['date'] = $cLetter;
                    }
                    if (in_array($val, ['c-in', 'in', 'masuk', 'jam_masuk', 'scan masuk', 'jam masuk'])) {
                        $colMap['check_in'] = $cLetter;
                    }
                    if (in_array($val, ['c-out', 'out', 'pulang', 'jam_pulang', 'scan keluar', 'jam pulang', 'keluar'])) {
                        $colMap['check_out'] = $cLetter;
                    }
                    if (in_array($val, ['status', 'status_kehadiran', 'keterangan_status'])) {
                        $colMap['status'] = $cLetter;
                    }
                    if (in_array($val, ['keterangan', 'notes', 'jam kerja', 'keterangan kerja', 'catatan'])) {
                        $colMap['notes'] = $cLetter;
                    }
                }

                if (isset($colMap['id']) && isset($colMap['date'])) {
                    break;
                }
            }

            // Ambil semua pengguna dari database untuk referensi pencocokan
            $users = User::all();
            $userByNip = $users->whereNotNull('nip')->keyBy(fn($u) => (string)$u->nip);
            $userById = $users->keyBy(fn($u) => (string)$u->id);
            $userByEmail = $users->keyBy(fn($u) => strtolower(trim($u->email)));
            $userByUsername = $users->whereNotNull('username')->keyBy(fn($u) => strtolower(trim($u->username)));
            $userByName = $users->keyBy(fn($u) => strtolower(trim($u->name)));

            // Ambil seluruh data absensi yang sudah ada untuk cek duplikasi
            $existingAttendances = Attendance::select('user_id', 'attendance_date')->get()
                ->groupBy('user_id')
                ->map(fn($items) => $items->pluck('attendance_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray());

            $previewItems = [];
            $totalRows = 0;
            $existingCount = 0;
            $newCount = 0;
            $unregisteredUsers = [];

            $dataRows = array_slice($rows, $headerRowIndex, null, true);

            foreach ($dataRows as $rIdx => $r) {
                $rawId = trim((string)($r[$colMap['id']] ?? ''));
                $rawName = trim((string)($r[$colMap['name']] ?? ''));
                $rawDate = trim((string)($r[$colMap['date']] ?? ''));

                // Lewati jika id dan tanggal kosong (misal baris petunjuk)
                if (empty($rawId) && empty($rawDate)) {
                    continue;
                }

                // Parse Tanggal
                $parsedDate = $this->parseDateValue($rawDate);
                if (!$parsedDate) {
                    continue;
                }

                $totalRows++;

                // Cari kecocokan user di sistem
                $matchedUser = $userByNip->get($rawId)
                    ?? $userById->get($rawId)
                    ?? $userByEmail->get(strtolower($rawId))
                    ?? $userByUsername->get(strtolower($rawId))
                    ?? $userByName->get(strtolower($rawName));

                // Parse Jam Masuk & Pulang
                $rawCheckIn = trim((string)($r[$colMap['check_in']] ?? ''));
                $rawCheckOut = trim((string)($r[$colMap['check_out']] ?? ''));
                $checkIn = $this->parseTimeValue($rawCheckIn);
                $checkOut = $this->parseTimeValue($rawCheckOut);

                // Tentukan Status Kehadiran
                $rawStatus = $colMap['status'] ? strtolower(trim((string)($r[$colMap['status']] ?? ''))) : '';
                $status = 'hadir';

                if (in_array($rawStatus, ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])) {
                    $status = $rawStatus;
                } else {
                    // Otomatis tentukan status berdasarkan jam masuk jadwal guru pada tanggal tersebut
                    if ($checkIn) {
                        $inTime = Carbon::createFromTimeString($checkIn);
                        
                        // Cek jam kerja guru pada hari tersebut
                        if ($matchedUser) {
                            $userSch = $matchedUser->getWorkScheduleForDate($parsedDate);
                            $schTimeIn = $userSch->time_in ?: '07:30:00';
                            $tolerance = $userSch->late_tolerance_minutes ?? 15;
                            $lateThreshold = Carbon::createFromTimeString($schTimeIn)->addMinutes($tolerance);
                        } else {
                            $lateThreshold = Carbon::createFromTimeString('07:30:00');
                        }

                        if ($inTime->greaterThan($lateThreshold)) {
                            $status = 'terlambat';
                        } else {
                            $status = 'hadir';
                        }
                    } elseif ($checkOut && !$checkIn) {
                        $status = 'hadir'; // Ada scan pulang
                    } else {
                        $status = 'alpa';
                    }
                }

                // Catatan
                $notes = $colMap['notes'] ? trim((string)($r[$colMap['notes']] ?? '')) : '';

                // Cek apakah tanggal & user ini sudah pernah ada di database
                $isExisting = false;
                if ($matchedUser) {
                    $userDates = $existingAttendances->get($matchedUser->id, []);
                    $isExisting = in_array($parsedDate, $userDates);
                }

                if ($isExisting) {
                    $existingCount++;
                } else {
                    $newCount++;
                }

                if (!$matchedUser && !empty($rawName)) {
                    $unregisteredUsers[$rawId ?: $rawName] = $rawName;
                }

                $previewItems[] = [
                    'row_id' => $rIdx,
                    'raw_id' => $rawId,
                    'raw_name' => $rawName,
                    'user_id' => $matchedUser?->id ?? null,
                    'matched_user_name' => $matchedUser?->name ?? null,
                    'matched_user_nip' => $matchedUser?->nip ?? null,
                    'date' => $parsedDate,
                    'formatted_date' => Carbon::parse($parsedDate)->translatedFormat('d M Y (l)'),
                    'check_in' => $checkIn ? substr($checkIn, 0, 5) : '',
                    'check_out' => $checkOut ? substr($checkOut, 0, 5) : '',
                    'status' => $status,
                    'notes' => $notes,
                    'is_existing' => $isExisting,
                    'is_registered' => (bool)$matchedUser,
                ];
            }

            return response()->json([
                'status' => 'success',
                'summary' => [
                    'total_rows' => count($previewItems),
                    'new_rows' => $newCount,
                    'existing_rows' => $existingCount,
                    'unregistered_count' => count($unregisteredUsers),
                    'unregistered_list' => array_values($unregisteredUsers),
                ],
                'items' => $previewItems,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membaca berkas Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * STEP 2: Simpan (Commit) Data Absensi Hasil Pratinjau ke Database
     */
    public function commitImport(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'auto_create_users' => ['nullable', 'boolean'],
            'overwrite' => ['nullable', 'boolean'],
        ], [
            'items.required' => 'Tidak ada baris data absensi yang dipilih untuk disimpan.',
        ]);

        $items = $request->input('items', []);
        $autoCreateUsers = $request->boolean('auto_create_users', true);
        $shouldOverwrite = $request->boolean('overwrite', true);

        $defaultRole = Role::where('name', 'user')->first() ?? Role::first();

        $savedCount = 0;
        $updatedCount = 0;
        $createdUserCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            $userCache = User::all()->keyBy('id');
            $userByNipCache = User::whereNotNull('nip')->get()->keyBy('nip');
            $userByNameCache = User::all()->keyBy(fn($u) => strtolower(trim($u->name)));

            foreach ($items as $item) {
                $userId = $item['user_id'] ?? null;
                $rawId = trim((string)($item['raw_id'] ?? ''));
                $rawName = trim((string)($item['raw_name'] ?? 'Pegawai'));
                $date = $item['date'] ?? null;

                if (empty($date)) {
                    $skippedCount++;
                    continue;
                }

                // 1. Dapatkan atau Buat User Pegawai jika belum terdaftar
                $targetUser = null;
                if ($userId && $userCache->has($userId)) {
                    $targetUser = $userCache->get($userId);
                } elseif (!empty($rawId) && $userByNipCache->has($rawId)) {
                    $targetUser = $userByNipCache->get($rawId);
                } elseif (!empty($rawName) && $userByNameCache->has(strtolower($rawName))) {
                    $targetUser = $userByNameCache->get(strtolower($rawName));
                } elseif ($autoCreateUsers && (!empty($rawId) || !empty($rawName))) {
                    // Otomatis daftarkan pegawai baru jika belum ada
                    $cleanUsername = Str::slug($rawName ?: 'user_' . $rawId, '_');
                    $baseUsername = $cleanUsername;
                    $counter = 1;
                    while (User::where('username', $cleanUsername)->exists()) {
                        $cleanUsername = $baseUsername . '_' . $counter++;
                    }

                    $generatedEmail = $cleanUsername . '@absensi.local';

                    $targetUser = User::create([
                        'role_id' => $defaultRole?->id,
                        'name' => $rawName ?: ('Pegawai ' . $rawId),
                        'username' => $cleanUsername,
                        'email' => $generatedEmail,
                        'nip' => $rawId ?: null,
                        'password' => Hash::make('password'),
                        'position' => 'Staff / Guru PAUD',
                        'department' => 'Operasional',
                        'is_active' => true,
                    ]);

                    $userCache->put($targetUser->id, $targetUser);
                    if ($targetUser->nip) {
                        $userByNipCache->put($targetUser->nip, $targetUser);
                    }
                    $userByNameCache->put(strtolower($targetUser->name), $targetUser);
                    $createdUserCount++;
                }

                if (!$targetUser) {
                    $skippedCount++;
                    continue;
                }

                // 2. Format Jam & Status
                $checkIn = !empty($item['check_in']) ? $this->parseTimeValue($item['check_in']) : null;
                $checkOut = !empty($item['check_out']) ? $this->parseTimeValue($item['check_out']) : null;
                $status = in_array(strtolower($item['status'] ?? ''), ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])
                    ? strtolower($item['status'])
                    : 'hadir';
                $notes = $item['notes'] ?? null;

                // 3. Cek apakah sudah ada data absensi pada tanggal tersebut
                $existing = Attendance::where('user_id', $targetUser->id)
                    ->where('attendance_date', $date)
                    ->first();

                if ($existing) {
                    if ($shouldOverwrite) {
                        $existing->update([
                            'check_in' => $checkIn ?: $existing->check_in,
                            'check_out' => $checkOut ?: $existing->check_out,
                            'status' => $status,
                            'notes' => $notes ?: $existing->notes,
                            'created_by' => Auth::id(),
                        ]);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    Attendance::create([
                        'user_id' => $targetUser->id,
                        'attendance_date' => $date,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                        'notes' => $notes,
                        'created_by' => Auth::id(),
                    ]);
                    $savedCount++;
                }
            }

            DB::commit();

            $msg = "Berhasil memasukkan {$savedCount} data absensi baru dan memperbarui {$updatedCount} data.";
            if ($createdUserCount > 0) {
                $msg .= " ({$createdUserCount} pegawai baru otomatis didaftarkan).";
            }

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'saved_count' => $savedCount,
                'updated_count' => $updatedCount,
                'created_user_count' => $createdUserCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data ke database: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ekspor data absensi terfilter ke berkas Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $query = Attendance::with(['user', 'creator']);

        if (!$user->isSuperAdmin() && !$user->canAccessMenu('users', 'view')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->input('status')));
        }

        $data = $query->orderBy('attendance_date', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        // Judul Laporan
        $sheet->setCellValue('A1', 'LAPORAN DATA PRESENSI / ABSENSI PEGAWAI');
        $sheet->setCellValue('A2', 'Periode: ' . ($startDate ? Carbon::parse($startDate)->format('d M Y') : 'Awal') . ' s/d ' . ($endDate ? Carbon::parse($endDate)->format('d M Y') : 'Sekarang'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('4B5563');

        // Header Tabel
        $headers = [
            'A4' => 'NO',
            'B4' => 'NIP / ID',
            'C4' => 'NAMA PEGAWAI',
            'D4' => 'JABATAN',
            'E4' => 'TANGGAL',
            'F4' => 'JAM MASUK',
            'G4' => 'JAM PULANG',
            'H4' => 'STATUS',
            'I4' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E1B4B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']],
            ],
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // Isi Data
        $row = 5;
        foreach ($data as $index => $item) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $item->user?->nip ?? $item->user?->id ?? '-');
            $sheet->setCellValue("C{$row}", $item->user?->name ?? 'User Terhapus');
            $sheet->setCellValue("D{$row}", $item->user?->position ?? '-');
            $sheet->setCellValue("E{$row}", $item->attendance_date?->format('Y-m-d') ?? '-');
            $sheet->setCellValue("F{$row}", $item->formatted_check_in);
            $sheet->setCellValue("G{$row}", $item->formatted_check_out);
            $sheet->setCellValue("H{$row}", strtoupper($item->status));
            $sheet->setCellValue("I{$row}", $item->notes ?? '-');

            // Format alignment
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Border
            $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');

            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data_Absensi_Export_' . date('Y-m-d_His') . '.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Helper parsing tanggal dari Excel / String
     */
    private function parseDateValue($rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        // Jika angka numerik serial date Excel (contoh: 45534)
        if (is_numeric($rawDate)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate))->format('Y-m-d');
            } catch (\Exception $e) {
                // Lanjut ke string parse
            }
        }

        $trimmed = trim((string)$rawDate);

        // Cek format MM/DD/YYYY atau MM-DD-YYYY (seperti pada Rekap_Fingerprint_PAUD.xlsx: 07/27/2026)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $trimmed, $matches)) {
            $m = (int)$matches[1];
            $d = (int)$matches[2];
            $y = (int)$matches[3];

            // Jika m > 12 dan d <= 12, kemungkinan format DD/MM/YYYY
            if ($m > 12 && $d <= 12) {
                return sprintf('%04d-%02d-%02d', $y, $d, $m);
            }

            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }

        try {
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper parsing waktu (Jam:Menit)
     */
    private function parseTimeValue($rawTime): ?string
    {
        if (empty($rawTime)) {
            return null;
        }

        // Jika angka numerik pecahan jam Excel (contoh: 0.3333 = 08:00)
        if (is_numeric($rawTime) && $rawTime > 0 && $rawTime <= 1) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTime))->format('H:i:s');
            } catch (\Exception $e) {
                // Lanjut ke string parse
            }
        }

        $cleaned = trim((string)$rawTime);

        // Jika 0 atau tanda strip, return null
        if ($cleaned === '0' || $cleaned === '-' || $cleaned === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $cleaned, $matches)) {
            $h = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $m = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $s = isset($matches[3]) ? str_pad($matches[3], 2, '0', STR_PAD_LEFT) : '00';
            return "{$h}:{$m}:{$s}";
        }

        try {
            return Carbon::parse($cleaned)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
