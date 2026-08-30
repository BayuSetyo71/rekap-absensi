<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTeachingSlot;
use App\Models\EmployeeWorkSchedule;
use App\Models\Unit;
use App\Models\UnitWorkSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkScheduleController extends Controller
{
    /**
     * Nama hari dalam bahasa Indonesia (1 = Senin s.d. 7 = Minggu)
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
     * Menampilkan halaman daftar jam kerja pegawai & master unit yayasan
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Ambil data master unit beserta jadwal standarnya
        $units = Unit::with('schedules')->where('is_active', true)->get();

        // 2. Query data pegawai/guru dengan eager loading relasi jadwal dan multi-slot mengajar
        $query = User::with([
            'role',
            'units',
            'workSchedules.unit',
            'teachingSlots.unit',
        ])->where('is_active', true);

        // Jika user biasa tanpa hak kelola, hanya tampilkan dirinya sendiri
        if (!$currentUser->isSuperAdmin() && !$currentUser->canAccessMenu('work-schedules', 'create') && !$currentUser->canAccessMenu('users', 'view')) {
            $query->where('id', $currentUser->id);
        }

        // Filter Unit
        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->where(function ($q) use ($unitId) {
                $q->whereHas('units', fn($uq) => $uq->where('units.id', $unitId))
                  ->orWhereHas('teachingSlots', fn($tq) => $tq->where('unit_id', $unitId));
            });
        }

        // Filter Tipe Penugasan
        if ($request->filled('assignment_type')) {
            $type = $request->input('assignment_type');
            if ($type === 'multi') {
                $query->where(function ($q) {
                    $q->has('units', '>', 1)
                      ->orWhereHas('teachingSlots', function ($tq) {
                          $tq->select('user_id')->groupBy('user_id')->havingRaw('COUNT(DISTINCT unit_id) > 1');
                      });
                });
            } elseif ($type === 'single') {
                $query->where(function ($q) {
                    $q->has('units', '=', 1)
                      ->whereDoesntHave('teachingSlots', function ($tq) {
                          $tq->select('user_id')->groupBy('user_id')->havingRaw('COUNT(DISTINCT unit_id) > 1');
                      });
                });
            } elseif ($type === 'unassigned') {
                $query->whereDoesntHave('teachingSlots')
                      ->whereDoesntHave('workSchedules', function ($wq) {
                          $wq->where('is_day_off', false)->whereNotNull('time_in');
                      });
            }
        }

        // Filter Pencarian Nama / NIP / Username / Jabatan
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Hitung Statistik KPI
        $allEmployees = (clone $query)->get();
        $unassignedCount = get_unconfigured_schedules_count();
        $unassignedEmployees = $allEmployees->filter(fn($u) => !$u->hasConfiguredSchedule())->count();
        $multiUnitCount = $allEmployees->filter(function ($u) {
            if (!$u->hasConfiguredSchedule()) return false;
            $distinctTeachingUnits = $u->teachingSlots->pluck('unit_id')->filter()->unique()->count();
            return $u->units->count() > 1 || $distinctTeachingUnits > 1;
        })->count();
        $singleUnitCount = $allEmployees->count() - $multiUnitCount - $unassignedEmployees;

        $stats = [
            'total_employees'  => $allEmployees->count(),
            'multi_unit'       => $multiUnitCount,
            'single_unit'      => max(0, $singleUnitCount),
            'unassigned'       => $unassignedCount,
            'total_units'      => $units->count(),
        ];

        // Paginate pegawai
        $employees = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('work-schedules.index', compact('employees', 'units', 'stats', 'unassignedCount'));
    }

    /**
     * Mengambil detail jadwal kerja, multi-slot mengajar, dan unit pegawai (AJAX JSON)
     */
    public function edit(User $user)
    {
        $user->load(['units', 'workSchedules.unit', 'teachingSlots.unit']);
        $units = Unit::with('schedules')->where('is_active', true)->get();

        $primaryUnit = $user->primary_unit;

        // Siapkan struktur 7 hari (Senin s.d. Minggu) beserta rincian multi-slot mengajar
        $daysData = [];
        $existingSchedules = $user->workSchedules->keyBy('day_of_week');

        foreach ($this->dayNames as $dayNum => $dayName) {
            // Ambil slot-slot mengajar pada hari tersebut
            $daySlots = $user->teachingSlots->where('day_of_week', $dayNum)->sortBy('start_time')->values();
            
            $formattedSlots = $daySlots->map(function ($slot, $idx) {
                return [
                    'id'          => $slot->id,
                    'unit_id'     => $slot->unit_id,
                    'unit_code'   => $slot->unit?->code ?? 'Unit',
                    'unit_name'   => $slot->unit?->name ?? 'Unit Sekolah',
                    'unit_color'  => $slot->unit?->color ?? '#4f46e5',
                    'start_time'  => $slot->formatted_start_time,
                    'end_time'    => $slot->formatted_end_time,
                    'subject'     => $slot->subject ?? '',
                    'notes'       => $slot->notes ?? '',
                    'order_index' => $idx + 1,
                ];
            })->toArray();

            // Cek apakah hari ini ada record jadwal harian (status libur atau fallback)
            $daySchedule = $existingSchedules->get($dayNum);
            $isOff = false;
            $notes = '';

            if ($daySchedule) {
                $isOff = (bool)$daySchedule->is_day_off || empty($daySchedule->time_in);
                $notes = $daySchedule->notes ?? '';
            } else {
                $isOff = !$user->hasConfiguredSchedule() || in_array($dayNum, [7]);
            }

            // Jika ada slot mengajar, maka hari tersebut aktif
            if (count($formattedSlots) > 0) {
                $isOff = false;
            }

            // Hitung jam masuk terawal dan jam pulang terakhir
            $earliestIn = '';
            $latestOut = '';
            if (count($formattedSlots) > 0) {
                $earliestIn = $formattedSlots[0]['start_time'];
                $latestOut = $formattedSlots[count($formattedSlots) - 1]['end_time'];
            } elseif ($daySchedule && !$isOff) {
                $earliestIn = $daySchedule->time_in ? substr($daySchedule->time_in, 0, 5) : '07:00';
                $latestOut = $daySchedule->time_out ? substr($daySchedule->time_out, 0, 5) : '14:00';
            }

            $daysData[$dayNum] = [
                'day_of_week'    => $dayNum,
                'day_name'       => $dayName,
                'is_day_off'     => $isOff,
                'earliest_in'    => $earliestIn,
                'latest_out'     => $latestOut,
                'notes'          => $notes,
                'slots'          => $formattedSlots,
            ];
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'nip'          => $user->nip ?: '-',
                'position'     => $user->position ?: '-',
                'department'   => $user->department ?: '-',
                'avatar_url'   => $user->avatar_url,
                'unit_ids'     => $user->units->pluck('id')->toArray(),
                'primary_unit' => $primaryUnit?->id,
                'is_configured'=> $user->hasConfiguredSchedule(),
            ],
            'days' => $daysData,
            'units' => $units,
        ]);
    }

    /**
     * Menyimpan penugasan unit dan pengaturan multi-slot jam mengajar harian guru
     */
    public function updateEmployeeSchedule(Request $request, User $user)
    {
        $request->validate([
            'unit_ids'                           => ['nullable', 'array'],
            'unit_ids.*'                         => ['exists:units,id'],
            'primary_unit_id'                    => ['nullable', 'exists:units,id'],
            'days'                               => ['required', 'array'],
            'days.*.day_of_week'                 => ['required', 'integer', 'between:1,7'],
            'days.*.is_day_off'                  => ['nullable'],
            'days.*.notes'                       => ['nullable', 'string', 'max:255'],
            'days.*.slots'                       => ['nullable', 'array'],
            'days.*.slots.*.unit_id'             => ['nullable', 'exists:units,id'],
            'days.*.slots.*.start_time'          => ['nullable', 'string'],
            'days.*.slots.*.end_time'            => ['nullable', 'string'],
            'days.*.slots.*.subject'             => ['nullable', 'string', 'max:100'],
            'days.*.slots.*.notes'               => ['nullable', 'string', 'max:255'],
        ], [
            'days.required' => 'Data jadwal harian wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan Relasi Unit yang Diampu (Multi-Unit)
            $unitIds = $request->input('unit_ids', []);
            $primaryUnitId = $request->input('primary_unit_id');

            // Kumpulkan juga unit_id yang digunakan pada slot-slot mengajar
            $daysInput = $request->input('days', []);
            foreach ($daysInput as $d) {
                if (!empty($d['slots']) && is_array($d['slots'])) {
                    foreach ($d['slots'] as $s) {
                        if (!empty($s['unit_id']) && !in_array($s['unit_id'], $unitIds)) {
                            $unitIds[] = (int)$s['unit_id'];
                        }
                    }
                }
            }

            if ($primaryUnitId && !in_array($primaryUnitId, $unitIds)) {
                $unitIds[] = (int)$primaryUnitId;
            }

            $syncData = [];
            foreach ($unitIds as $uId) {
                $syncData[$uId] = [
                    'is_primary' => ($primaryUnitId == $uId) || (count($unitIds) === 1),
                ];
            }
            $user->units()->sync($syncData);

            // 2. Hapus slot mengajar lama untuk user ini, lalu isi dengan yang baru
            EmployeeTeachingSlot::where('user_id', $user->id)->delete();

            // 3. Simpan Jadwal Harian dan Slot Mengajar (Senin s.d. Minggu)
            foreach ($daysInput as $dayNum => $row) {
                $dayOfWeek = (int)($row['day_of_week'] ?? $dayNum);
                $isDayOff = isset($row['is_day_off']) && ($row['is_day_off'] === '1' || $row['is_day_off'] === true || $row['is_day_off'] === 'true');
                $dayNotes = $row['notes'] ?? null;

                $slotsData = $row['slots'] ?? [];
                $validSlots = [];

                if (!$isDayOff && is_array($slotsData)) {
                    $order = 1;
                    foreach ($slotsData as $slot) {
                        $startTime = !empty($slot['start_time']) ? substr($slot['start_time'], 0, 5) . ':00' : null;
                        $endTime = !empty($slot['end_time']) ? substr($slot['end_time'], 0, 5) . ':00' : null;
                        $slotUnitId = !empty($slot['unit_id']) ? (int)$slot['unit_id'] : ($primaryUnitId ?: ($unitIds[0] ?? null));
                        $subject = !empty($slot['subject']) ? trim($slot['subject']) : null;
                        $slotNotes = !empty($slot['notes']) ? trim($slot['notes']) : null;

                        if ($startTime && $endTime) {
                            $newSlot = EmployeeTeachingSlot::create([
                                'user_id'     => $user->id,
                                'day_of_week' => $dayOfWeek,
                                'unit_id'     => $slotUnitId,
                                'start_time'  => $startTime,
                                'end_time'    => $endTime,
                                'subject'     => $subject,
                                'notes'       => $slotNotes,
                                'order_index' => $order++,
                            ]);
                            $validSlots[] = $newSlot;
                        }
                    }
                }

                // Hitung jam masuk & pulang harian
                if (count($validSlots) > 0) {
                    $isDayOff = false;
                    $sortedSlots = collect($validSlots)->sortBy('start_time');
                    $timeIn = $sortedSlots->first()->start_time;
                    $timeOut = $sortedSlots->sortByDesc('end_time')->first()->end_time;
                    $dailyUnitId = $sortedSlots->first()->unit_id;
                    $dayNotes = collect($validSlots)->pluck('subject')->filter()->implode(', ') ?: $dayNotes;
                } else {
                    $isDayOff = true;
                    $timeIn = null;
                    $timeOut = null;
                    $dailyUnitId = null;
                    $dayNotes = 'Libur / Tidak Mengajar';
                }

                // Simpan atau update employee_work_schedules sebagai ringkasan harian
                EmployeeWorkSchedule::updateOrCreate(
                    [
                        'user_id'     => $user->id,
                        'day_of_week' => $dayOfWeek,
                    ],
                    [
                        'day_name'               => $this->dayNames[$dayOfWeek] ?? 'Hari',
                        'unit_id'                => $dailyUnitId,
                        'schedule_type'          => $isDayOff ? 'off' : 'custom',
                        'time_in'                => $timeIn,
                        'time_out'               => $timeOut,
                        'late_tolerance_minutes' => 15,
                        'is_day_off'             => $isDayOff,
                        'notes'                  => $dayNotes,
                    ]
                );
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Pengaturan jadwal mengajar untuk {$user->name} berhasil disimpan.",
                ]);
            }

            return redirect()->route('work-schedules.index')->with('success', "Pengaturan jadwal mengajar untuk {$user->name} berhasil disimpan.");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan jadwal: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui informasi master unit yayasan (Nama, Jam Masuk/Pulang Default, Toleransi)
     */
    public function updateUnit(Request $request, Unit $unit)
    {
        $request->validate([
            'name'                   => ['required', 'string', 'max:100'],
            'color'                  => ['required', 'string', 'max:20'],
            'default_time_in'        => ['required', 'string'],
            'default_time_out'       => ['required', 'string'],
            'default_late_tolerance' => ['required', 'integer', 'min:0', 'max:120'],
            'description'            => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama unit wajib diisi.',
            'default_time_in.required' => 'Jam masuk default wajib diisi.',
            'default_time_out.required' => 'Jam pulang default wajib diisi.',
        ]);

        $unit->update([
            'name'                   => $request->input('name'),
            'color'                  => $request->input('color'),
            'default_time_in'        => substr($request->input('default_time_in'), 0, 5) . ':00',
            'default_time_out'       => substr($request->input('default_time_out'), 0, 5) . ':00',
            'default_late_tolerance' => (int)$request->input('default_late_tolerance'),
            'description'            => $request->input('description'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Master unit {$unit->name} berhasil diperbarui.",
                'unit'    => $unit,
            ]);
        }

        return redirect()->route('work-schedules.index')->with('success', "Master unit {$unit->name} berhasil diperbarui.");
    }

    /**
     * Memperbarui jadwal kerja standar per hari pada unit tertentu
     */
    public function updateUnitSchedule(Request $request, Unit $unit)
    {
        $request->validate([
            'schedules'                  => ['required', 'array'],
            'schedules.*.day_of_week'    => ['required', 'integer', 'between:1,7'],
            'schedules.*.time_in'        => ['nullable', 'string'],
            'schedules.*.time_out'       => ['nullable', 'string'],
            'schedules.*.late_tolerance' => ['nullable', 'integer', 'min:0', 'max:120'],
            'schedules.*.is_day_off'     => ['nullable'],
            'schedules.*.notes'          => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->input('schedules', []) as $dayNum => $row) {
                $dayOfWeek = (int)($row['day_of_week'] ?? $dayNum);
                $isDayOff = isset($row['is_day_off']) && ($row['is_day_off'] === '1' || $row['is_day_off'] === true || $row['is_day_off'] === 'true');
                
                $timeIn = (!empty($row['time_in']) && !$isDayOff) ? substr($row['time_in'], 0, 5) . ':00' : null;
                $timeOut = (!empty($row['time_out']) && !$isDayOff) ? substr($row['time_out'], 0, 5) . ':00' : null;
                $tolerance = isset($row['late_tolerance']) ? (int)$row['late_tolerance'] : $unit->default_late_tolerance;
                $notes = $row['notes'] ?? null;

                UnitWorkSchedule::updateOrCreate(
                    [
                        'unit_id'     => $unit->id,
                        'day_of_week' => $dayOfWeek,
                    ],
                    [
                        'day_name'               => $this->dayNames[$dayOfWeek] ?? 'Hari',
                        'time_in'                => $timeIn,
                        'time_out'               => $timeOut,
                        'late_tolerance_minutes' => $tolerance,
                        'is_day_off'             => $isDayOff,
                        'notes'                  => $notes,
                    ]
                );
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Jadwal standar unit {$unit->name} berhasil diperbarui.",
                ]);
            }

            return redirect()->route('work-schedules.index')->with('success', "Jadwal standar unit {$unit->name} berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memperbarui jadwal unit: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal memperbarui jadwal unit: ' . $e->getMessage());
        }
    }

    /**
     * Terapkan preset jadwal standar unit secara massal ke pegawai yang dipilih
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'unit_id' => ['required', 'exists:units,id'],
        ], [
            'user_ids.required' => 'Pilih minimal satu pegawai.',
            'unit_id.required'  => 'Pilih unit yang akan diterapkan.',
        ]);

        $unit = Unit::with('schedules')->findOrFail($request->input('unit_id'));
        $userIds = $request->input('user_ids', []);

        DB::beginTransaction();

        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                // Sync unit utama
                $user->units()->sync([$unit->id => ['is_primary' => true]]);

                // Hapus slot mengajar kustom lama
                EmployeeTeachingSlot::where('user_id', $user->id)->delete();

                // Generate jadwal harian standar sesuai unit
                foreach ($this->dayNames as $dayNum => $dayName) {
                    $unitSch = $unit->schedules->firstWhere('day_of_week', $dayNum);
                    $isOff = $unitSch ? (bool)$unitSch->is_day_off : in_array($dayNum, [7]);

                    EmployeeWorkSchedule::updateOrCreate(
                        [
                            'user_id'     => $user->id,
                            'day_of_week' => $dayNum,
                        ],
                        [
                            'day_name'               => $dayName,
                            'unit_id'                => $unit->id,
                            'schedule_type'          => $isOff ? 'off' : 'default_unit',
                            'time_in'                => $unitSch?->time_in ?? ($isOff ? null : $unit->default_time_in),
                            'time_out'               => $unitSch?->time_out ?? ($isOff ? null : $unit->default_time_out),
                            'late_tolerance_minutes' => $unitSch?->late_tolerance_minutes ?? $unit->default_late_tolerance,
                            'is_day_off'             => $isOff,
                            'notes'                  => $unitSch?->notes ?? ('Standar Unit ' . $unit->name),
                        ]
                    );

                    // Buat 1 slot standar mengajar jika hari aktif
                    if (!$isOff && $unitSch && $unitSch->time_in && $unitSch->time_out) {
                        EmployeeTeachingSlot::create([
                            'user_id'     => $user->id,
                            'day_of_week' => $dayNum,
                            'unit_id'     => $unit->id,
                            'start_time'  => $unitSch->time_in,
                            'end_time'    => $unitSch->time_out,
                            'subject'     => 'Kegiatan Belajar ' . $unit->name,
                            'notes'       => $unitSch->notes,
                            'order_index' => 1,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil menerapkan jadwal standar {$unit->name} untuk " . count($userIds) . " pegawai.",
                ]);
            }

            return redirect()->route('work-schedules.index')->with('success', "Berhasil menerapkan jadwal standar {$unit->name} untuk " . count($userIds) . " pegawai.");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ekspor data matriks jam kerja pegawai dan sesi mengajar ke Excel
     */
    public function exportExcel(Request $request)
    {
        $query = User::with(['role', 'units', 'workSchedules.unit', 'teachingSlots.unit'])->where('is_active', true);

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->whereHas('units', function ($q) use ($unitId) {
                $q->where('units.id', $unitId);
            });
        }

        $employees = $query->orderBy('name', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jam Kerja Pegawai');

        // Judul Laporan
        $sheet->setCellValue('A1', 'MATRIKS JADWAL JAM KERJA & SESI MENGAJAR GURU YAYASAN');
        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y, H:i') . ' WIB');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7280');

        // Header Tabel
        $headers = [
            'A4' => 'NO',
            'B4' => 'NIP',
            'C4' => 'NAMA LENGKAP',
            'D4' => 'JABATAN',
            'E4' => 'UNIT YANG DIAMPU',
            'F4' => 'SENIN',
            'G4' => 'SELASA',
            'H4' => 'RABU',
            'I4' => 'KAMIS',
            'J4' => 'JUMAT',
            'K4' => 'SABTU',
            'L4' => 'MINGGU',
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
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '9CA3AF']],
            ],
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Isi Baris Data Pegawai
        $row = 5;
        foreach ($employees as $idx => $emp) {
            $unitsStr = $emp->units->pluck('name')->implode(', ') ?: ($emp->hasConfiguredSchedule() ? 'Umum' : 'Belum Diatur');

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $emp->nip ?: '-');
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->position ?: '-');
            $sheet->setCellValue("E{$row}", $unitsStr);

            $dayCols = [1 => 'F', 2 => 'G', 3 => 'H', 4 => 'I', 5 => 'J', 6 => 'K', 7 => 'L'];

            foreach ($dayCols as $dayNum => $col) {
                $sch = $emp->getWorkScheduleForDay($dayNum);
                
                if (!$emp->hasConfiguredSchedule()) {
                    $cellVal = 'Belum Diatur';
                } elseif ($sch->is_day_off) {
                    $cellVal = 'LIBUR';
                } elseif ($sch->slots && $sch->slots->isNotEmpty()) {
                    $slotTexts = [];
                    foreach ($sch->slots as $s) {
                        $uCode = $s->unit ? $s->unit->code : 'Unit';
                        $sub = $s->subject ? " ({$s->subject})" : '';
                        $slotTexts[] = "[{$uCode} {$s->formatted_start_time}-{$s->formatted_end_time}{$sub}]";
                    }
                    $cellVal = implode("\n", $slotTexts);
                } else {
                    $in = substr($sch->time_in, 0, 5);
                    $out = substr($sch->time_out, 0, 5);
                    $uCode = $sch->unit ? " ({$sch->unit->code})" : '';
                    $cellVal = "{$in} - {$out}{$uCode}";
                }

                $sheet->setCellValue("{$col}{$row}", $cellVal);
            }

            // Alignment & wrapping
            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A{$row}:L{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');

            $row++;
        }

        foreach (range('A', 'L') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Jadwal_Mengajar_Pegawai_' . date('Y-m-d_His') . '.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
