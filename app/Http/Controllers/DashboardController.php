<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan Portal Menu Utama (App Launcher & Modul Terkelompok Berdasarkan Role Pengguna)
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        // 1. Deskripsi & Metadata visual yang kaya untuk setiap modul
        $moduleMetadata = [
            'reports' => [
                'description' => 'Laporan rekapitulasi kehadiran bulanan, visualisasi performa, dan ekspor data ke Excel & PDF.',
                'gradient'    => 'linear-gradient(135deg, #059669, #047857)',
                'badge'       => 'Laporan & Ekspor',
                'badge_class' => 'badge-subtle-success',
                'category'    => 'Presensi & Absensi',
                'create_url'  => null,
                'export_url'  => route('reports.export-excel'),
            ],
            'attendances' => [
                'description' => 'Injeksi file log fingerprint Excel/CSV, manajemen kehadiran harian, serta validasi status masuk & pulang.',
                'gradient'    => 'linear-gradient(135deg, #4f46e5, #3730a3)',
                'badge'       => 'Operasional Presensi',
                'badge_class' => 'badge-subtle-primary',
                'category'    => 'Presensi & Absensi',
                'create_url'  => null,
                'export_url'  => route('attendances.export'),
            ],
            'attendance-recap' => [
                'description' => 'Rekapitulasi absensi per pegawai, tracking keterlambatan & kepulangan, riwayat detail, dan multi-grafik.',
                'gradient'    => 'linear-gradient(135deg, #7c3aed, #5b21b6)',
                'badge'       => 'Analisis Kehadiran',
                'badge_class' => 'badge-subtle-primary',
                'category'    => 'Presensi & Absensi',
                'create_url'  => null,
                'export_url'  => route('attendance-recap.export'),
            ],
            'schedule-info' => [
                'description' => 'Monitoring jadwal mengajar harian & mingguan guru lintas unit TK, SD, SMP, SMA, serta ekspor jadwal.',
                'gradient'    => 'linear-gradient(135deg, #0891b2, #0e7490)',
                'badge'       => 'Monitoring Jadwal',
                'badge_class' => 'badge-subtle-info',
                'category'    => 'Jadwal & Jam Kerja',
                'create_url'  => null,
                'export_url'  => route('schedule-info.export'),
            ],
            'work-schedules' => [
                'description' => 'Konfigurasi jam kerja reguler & sesi mengajar per unit yayasan, shift kerja, dan penetapan jadwal pegawai.',
                'gradient'    => 'linear-gradient(135deg, #d97706, #b45309)',
                'badge'       => 'Konfigurasi Kerja',
                'badge_class' => 'badge-subtle-warning',
                'category'    => 'Jadwal & Jam Kerja',
                'create_url'  => null,
                'export_url'  => route('work-schedules.export'),
            ],
            'users' => [
                'description' => 'Manajemen basis data pegawai, NIP, unit jenjang, jabatan, departemen, dan aktivasi akun sistem.',
                'gradient'    => 'linear-gradient(135deg, #2563eb, #1d4ed8)',
                'badge'       => 'Master Kepegawaian',
                'badge_class' => 'badge-subtle-primary',
                'category'    => 'Master & Pengaturan',
                'create_url'  => null,
                'export_url'  => route('users.export'),
            ],
            'roles' => [
                'description' => 'Pengaturan peran sistem dan matriks hak akses menu granular (Lihat, Tambah, Edit, Hapus, Ekspor).',
                'gradient'    => 'linear-gradient(135deg, #e11d48, #be123c)',
                'badge'       => 'Keamanan & Hak Akses',
                'badge_class' => 'badge-subtle-danger',
                'category'    => 'Master & Pengaturan',
                'create_url'  => null,
                'export_url'  => null,
            ],
            'menus' => [
                'description' => 'Struktur navigasi hierarkis, pendaftaran modul baru, icon styling, dan pengaturan urutan menu dinamis.',
                'gradient'    => 'linear-gradient(135deg, #475569, #334155)',
                'badge'       => 'Struktur Sistem',
                'badge_class' => 'badge-subtle-info',
                'category'    => 'Master & Pengaturan',
                'create_url'  => null,
                'export_url'  => null,
            ],
        ];

        // 2. Ambil seluruh menu utama dan sub-menu yang diizinkan untuk user
        $accessibleGroups = get_user_menus();

        // 3. Transformasi ke koleksi grup dan modul yang rapi
        $menuGroups = [];
        $totalAccessibleModules = 0;

        foreach ($accessibleGroups as $group) {
            // Lewati menu 'dashboard' itu sendiri agar tidak redundan di dalam grid modul
            if ($group->code === 'dashboard') {
                continue;
            }

            $groupItems = [];

            if ($group->children->isNotEmpty()) {
                foreach ($group->children as $child) {
                    $meta = $moduleMetadata[$child->code] ?? [
                        'description' => 'Akses dan kelola data modul ' . $child->name . '.',
                        'gradient'    => 'linear-gradient(135deg, #4f46e5, #3730a3)',
                        'badge'       => 'Modul Terdaftar',
                        'badge_class' => 'badge-subtle-primary',
                        'category'    => $group->name,
                        'create_url'  => null,
                        'export_url'  => null,
                    ];

                    $canCreate = $isSuperAdmin || $user->canAccessMenu($child->id, 'create');
                    $canUpdate = $isSuperAdmin || $user->canAccessMenu($child->id, 'update');
                    $canDelete = $isSuperAdmin || $user->canAccessMenu($child->id, 'delete');
                    $canExport = $isSuperAdmin || $user->canAccessMenu($child->id, 'export');

                    $accessLevel = 'Akses Terbatas';
                    if ($isSuperAdmin || ($canCreate && $canUpdate && $canDelete)) {
                        $accessLevel = 'Akses Penuh (CRUD)';
                    } elseif ($canUpdate || $canCreate) {
                        $accessLevel = 'Kelola & Update';
                    } elseif ($canExport) {
                        $accessLevel = 'Lihat & Ekspor';
                    } else {
                        $accessLevel = 'Hanya Lihat';
                    }

                    $groupItems[] = [
                        'id'           => $child->id,
                        'code'         => $child->code,
                        'name'         => $child->name,
                        'url'          => $child->link,
                        'icon'         => $child->icon ?: 'bi bi-app-indicator',
                        'description'  => $meta['description'],
                        'gradient'     => $meta['gradient'],
                        'badge'        => $meta['badge'],
                        'badge_class'  => $meta['badge_class'],
                        'access_level' => $accessLevel,
                        'can_create'   => $canCreate && $child->has_create,
                        'can_export'   => $canExport && $child->has_export,
                        'export_url'   => $meta['export_url'],
                    ];
                    $totalAccessibleModules++;
                }
            } else {
                // Menu tunggal tanpa anak
                $meta = $moduleMetadata[$group->code] ?? [
                    'description' => 'Akses modul ' . $group->name . '.',
                    'gradient'    => 'linear-gradient(135deg, #4f46e5, #3730a3)',
                    'badge'       => 'Modul',
                    'badge_class' => 'badge-subtle-primary',
                    'category'    => $group->name,
                    'create_url'  => null,
                    'export_url'  => null,
                ];

                $groupItems[] = [
                    'id'           => $group->id,
                    'code'         => $group->code,
                    'name'         => $group->name,
                    'url'          => $group->link,
                    'icon'         => $group->icon ?: 'bi bi-app-indicator',
                    'description'  => $meta['description'],
                    'gradient'     => $meta['gradient'],
                    'badge'        => $meta['badge'],
                    'badge_class'  => $meta['badge_class'],
                    'access_level' => 'Akses Aktif',
                    'can_create'   => false,
                    'can_export'   => false,
                    'export_url'   => null,
                ];
                $totalAccessibleModules++;
            }

            if (!empty($groupItems)) {
                $menuGroups[] = [
                    'group_id'   => $group->id,
                    'group_name' => $group->name,
                    'group_icon' => $group->icon ?: 'bi bi-folder-fill',
                    'items'      => $groupItems,
                ];
            }
        }

        // 4. Modul Akun Personal (Selalu ada untuk setiap user yang login)
        $accountModule = [
            'name'         => 'Profil & Keamanan Akun',
            'url'          => route('profile.index'),
            'icon'         => 'bi bi-person-gear',
            'description'  => 'Perbarui informasi profil pegawai, nomor kontak, foto avatar, dan ubah kata sandi akun.',
            'gradient'     => 'linear-gradient(135deg, #0284c7, #0369a1)',
            'badge'        => 'Akun Personal',
            'badge_class'  => 'badge-subtle-info',
            'access_level' => 'Akses Pribadi',
        ];

        // 5. Statistik Ringkas Sistem untuk Header Portal
        $stats = [
            'total_modules'     => $totalAccessibleModules,
            'total_groups'      => count($menuGroups),
            'role_name'         => $user->role?->display_name ?? 'User',
            'unconfigured_count'=> get_unconfigured_schedules_count(),
        ];

        // 6. Data Khusus Dashboard Karyawan / Guru
        $isEmployee = !$isSuperAdmin && $user->hasRole('user');
        $employeeData = null;

        if ($isEmployee || true) { // Siapkan data agar fleksibel
            $todayDate = \Carbon\Carbon::now()->toDateString();
            $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
                ->where('attendance_date', $todayDate)
                ->first();

            $todayDayIso = (int)\Carbon\Carbon::now()->dayOfWeekIso; // 1 = Senin s.d. 7 = Minggu
            $todaySlots = $user->teachingSlots()
                ->with('unit')
                ->where('day_of_week', $todayDayIso)
                ->orderBy('start_time', 'asc')
                ->get();

            $todaySchedule = $user->getWorkScheduleForDate($todayDate);

            // Statistik Kehadiran Bulan Berjalan
            $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->toDateString();
            $monthlyAttendances = \App\Models\Attendance::where('user_id', $user->id)
                ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
                ->get();

            $monthlyPresent = $monthlyAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $monthlyLate = $monthlyAttendances->where('status', 'terlambat')->count();
            $monthlyPermit = $monthlyAttendances->whereIn('status', ['izin', 'sakit'])->count();
            $monthlyTotalLogs = $monthlyAttendances->count();
            $monthlyPercentage = $monthlyTotalLogs > 0 ? round(($monthlyPresent / $monthlyTotalLogs) * 100, 1) : 0;

            // Slip Gaji Terakhir
            $latestPayroll = \App\Models\Payroll::where('user_id', $user->id)
                ->orderBy('period_month', 'desc')
                ->first();

            $employeeData = [
                'today_attendance'    => $todayAttendance,
                'today_slots'         => $todaySlots,
                'today_schedule'      => $todaySchedule,
                'monthly_present'     => $monthlyPresent,
                'monthly_late'        => $monthlyLate,
                'monthly_permit'      => $monthlyPermit,
                'monthly_percentage'  => $monthlyPercentage,
                'latest_payroll'      => $latestPayroll,
                'total_weekly_slots'  => $user->teachingSlots->count(),
                'total_weekly_hours'  => round($user->teachingSlots->sum(fn($slot) => $slot->duration_minutes) / 60, 1),
            ];
        }

        return view('dashboard.index', compact(
            'user',
            'menuGroups',
            'accountModule',
            'stats',
            'isSuperAdmin',
            'isEmployee',
            'employeeData'
        ));
    }
}
