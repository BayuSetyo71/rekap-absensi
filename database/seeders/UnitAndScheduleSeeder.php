<?php

namespace Database\Seeders;

use App\Models\EmployeeTeachingSlot;
use App\Models\EmployeeWorkSchedule;
use App\Models\Role;
use App\Models\Unit;
use App\Models\UnitWorkSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnitAndScheduleSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk master unit, jadwal kerja unit, dan penugasan awal pegawai.
     */
    public function run(): void
    {
        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        // 1. Master Data Unit Sekolah di Yayasan
        $unitsData = [
            [
                'code' => 'TK',
                'name' => 'TK / PAUD',
                'color' => '#10b981', // Hijau Emerald
                'default_time_in' => '07:30:00',
                'default_time_out' => '11:30:00',
                'default_late_tolerance' => 15,
                'is_active' => true,
                'description' => 'Jenjang Taman Kanak-Kanak / Pendidikan Anak Usia Dini',
                'schedules' => [
                    1 => ['in' => '07:30:00', 'out' => '11:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar TK'],
                    2 => ['in' => '07:30:00', 'out' => '11:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar TK'],
                    3 => ['in' => '07:30:00', 'out' => '11:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar TK'],
                    4 => ['in' => '07:30:00', 'out' => '11:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar TK'],
                    5 => ['in' => '07:30:00', 'out' => '11:00:00', 'off' => false, 'notes' => 'Jumat Bersih & Belajar'],
                    6 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                    7 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                ]
            ],
            [
                'code' => 'SD',
                'name' => 'Sekolah Dasar (SD)',
                'color' => '#3b82f6', // Biru
                'default_time_in' => '07:00:00',
                'default_time_out' => '13:30:00',
                'default_late_tolerance' => 15,
                'is_active' => true,
                'description' => 'Jenjang Sekolah Dasar',
                'schedules' => [
                    1 => ['in' => '07:00:00', 'out' => '13:30:00', 'off' => false, 'notes' => 'Upacara & Belajar Mengajar'],
                    2 => ['in' => '07:00:00', 'out' => '13:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SD'],
                    3 => ['in' => '07:00:00', 'out' => '13:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SD'],
                    4 => ['in' => '07:00:00', 'out' => '13:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SD'],
                    5 => ['in' => '07:00:00', 'out' => '11:30:00', 'off' => false, 'notes' => 'Jumat Ibadah & Belajar'],
                    6 => ['in' => '07:00:00', 'out' => '12:00:00', 'off' => false, 'notes' => 'Ekstrakurikuler & Pembinaan'],
                    7 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                ]
            ],
            [
                'code' => 'SMP',
                'name' => 'Sekolah Menengah Pertama (SMP)',
                'color' => '#8b5cf6', // Ungu / Violet
                'default_time_in' => '07:00:00',
                'default_time_out' => '14:30:00',
                'default_late_tolerance' => 15,
                'is_active' => true,
                'description' => 'Jenjang Sekolah Menengah Pertama',
                'schedules' => [
                    1 => ['in' => '07:00:00', 'out' => '14:30:00', 'off' => false, 'notes' => 'Upacara & Belajar Mengajar'],
                    2 => ['in' => '07:00:00', 'out' => '14:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMP'],
                    3 => ['in' => '07:00:00', 'out' => '14:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMP'],
                    4 => ['in' => '07:00:00', 'out' => '14:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMP'],
                    5 => ['in' => '07:00:00', 'out' => '11:45:00', 'off' => false, 'notes' => 'Jumat Ibadah & Belajar'],
                    6 => ['in' => '07:00:00', 'out' => '12:30:00', 'off' => false, 'notes' => 'Pengembangan Diri & Ekskul'],
                    7 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                ]
            ],
            [
                'code' => 'SMA',
                'name' => 'Sekolah Menengah Atas (SMA)',
                'color' => '#f59e0b', // Amber / Oranye
                'default_time_in' => '07:00:00',
                'default_time_out' => '15:30:00',
                'default_late_tolerance' => 15,
                'is_active' => true,
                'description' => 'Jenjang Sekolah Menengah Atas / Kejuruan',
                'schedules' => [
                    1 => ['in' => '07:00:00', 'out' => '15:30:00', 'off' => false, 'notes' => 'Upacara & Belajar Mengajar'],
                    2 => ['in' => '07:00:00', 'out' => '15:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMA'],
                    3 => ['in' => '07:00:00', 'out' => '15:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMA'],
                    4 => ['in' => '07:00:00', 'out' => '15:30:00', 'off' => false, 'notes' => 'Kegiatan Belajar SMA'],
                    5 => ['in' => '07:00:00', 'out' => '11:45:00', 'off' => false, 'notes' => 'Jumat Ibadah & Belajar'],
                    6 => ['in' => '07:00:00', 'out' => '13:00:00', 'off' => false, 'notes' => 'Praktikum & Ekstrakurikuler'],
                    7 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                ]
            ],
            [
                'code' => 'YAYASAN',
                'name' => 'Kantor Yayasan & Staff',
                'color' => '#6366f1', // Indigo
                'default_time_in' => '07:30:00',
                'default_time_out' => '16:00:00',
                'default_late_tolerance' => 15,
                'is_active' => true,
                'description' => 'Kantor Manajemen Yayasan, Tata Usaha & Operasional',
                'schedules' => [
                    1 => ['in' => '07:30:00', 'out' => '16:00:00', 'off' => false, 'notes' => 'Operasional Yayasan'],
                    2 => ['in' => '07:30:00', 'out' => '16:00:00', 'off' => false, 'notes' => 'Operasional Yayasan'],
                    3 => ['in' => '07:30:00', 'out' => '16:00:00', 'off' => false, 'notes' => 'Operasional Yayasan'],
                    4 => ['in' => '07:30:00', 'out' => '16:00:00', 'off' => false, 'notes' => 'Operasional Yayasan'],
                    5 => ['in' => '07:30:00', 'out' => '16:00:00', 'off' => false, 'notes' => 'Operasional Yayasan'],
                    6 => ['in' => '08:00:00', 'out' => '13:00:00', 'off' => false, 'notes' => 'Piket Pelayanan Yayasan'],
                    7 => ['in' => null, 'out' => null, 'off' => true, 'notes' => 'Libur Akhir Pekan'],
                ]
            ],
        ];

        $createdUnits = [];

        foreach ($unitsData as $item) {
            $schedules = $item['schedules'];
            unset($item['schedules']);

            $unit = Unit::updateOrCreate(['code' => $item['code']], $item);
            $createdUnits[$unit->code] = $unit;

            foreach ($schedules as $dayNum => $sch) {
                UnitWorkSchedule::updateOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'day_of_week' => $dayNum,
                    ],
                    [
                        'day_name' => $dayNames[$dayNum],
                        'time_in' => $sch['in'],
                        'time_out' => $sch['out'],
                        'late_tolerance_minutes' => $unit->default_late_tolerance,
                        'is_day_off' => $sch['off'],
                        'notes' => $sch['notes'],
                    ]
                );
            }
        }

        // 2. Hubungkan Pegawai Demo ke Unit
        // a. Super Admin & Admin HRD -> Yayasan
        $adminUsers = User::whereIn('username', ['superadmin', 'adminhrd'])->get();
        foreach ($adminUsers as $admin) {
            $admin->units()->syncWithoutDetaching([
                $createdUnits['YAYASAN']->id => ['is_primary' => true]
            ]);
        }

        // b. Ahmad Fauzi / Guru Bayu -> Guru Multi-Jenjang Fleksibel Multi-Sesi dalam 1 Hari (TK, SD, SMP, SMA)
        $guruFauzi = User::where('username', 'ahmadfauzi')->first();
        if ($guruFauzi) {
            $guruFauzi->update([
                'position' => 'Guru IT & Komputer (TK - SMA)',
                'department' => 'Tenaga Pendidik',
            ]);

            $guruFauzi->units()->sync([
                $createdUnits['TK']->id => ['is_primary' => true],
                $createdUnits['SD']->id => ['is_primary' => false],
                $createdUnits['SMP']->id => ['is_primary' => false],
                $createdUnits['SMA']->id => ['is_primary' => false],
            ]);

            // Hapus slot mengajar lama
            EmployeeTeachingSlot::where('user_id', $guruFauzi->id)->delete();

            // Skenario Fleksibel Multi-Sesi per Hari:
            // SENIN: 3 Sesi (SD: 07:30-08:30, SMP: 08:30-09:30, SMA: 10:00-11:30)
            // SELASA: 2 Sesi (TK: 07:30-09:00, SD: 09:30-11:30)
            // RABU: 1 Sesi (SMP: 07:30-10:30)
            // KAMIS: 1 Sesi (SMA: 07:30-11:30)
            // JUMAT: 1 Sesi (SD: 07:30-10:00)
            // SABTU & MINGGU: Libur
            $teachingSlotsData = [
                // SENIN
                ['day' => 1, 'unit' => 'SD', 'start' => '07:30:00', 'end' => '08:30:00', 'subject' => 'IT & Komputer SD Kelas 4-6', 'notes' => 'Lab Komputer SD'],
                ['day' => 1, 'unit' => 'SMP', 'start' => '08:30:00', 'end' => '09:30:00', 'subject' => 'IT & Informatika SMP Kelas 7', 'notes' => 'Lab Komputer SMP'],
                ['day' => 1, 'unit' => 'SMA', 'start' => '10:00:00', 'end' => '11:30:00', 'subject' => 'Coding & Pemrograman SMA Kelas 10', 'notes' => 'Lab Komputer SMA'],

                // SELASA
                ['day' => 2, 'unit' => 'TK', 'start' => '07:30:00', 'end' => '09:00:00', 'subject' => 'Pengenalan Komputer & Tablet TK', 'notes' => 'Ruang Multimedia TK'],
                ['day' => 2, 'unit' => 'SD', 'start' => '09:30:00', 'end' => '11:30:00', 'subject' => 'IT & Desain Grafis SD Kelas 5', 'notes' => 'Lab Komputer SD'],

                // RABU
                ['day' => 3, 'unit' => 'SMP', 'start' => '07:30:00', 'end' => '10:30:00', 'subject' => 'Informatika & Robotik SMP Kelas 8', 'notes' => 'Lab Robotik SMP'],

                // KAMIS
                ['day' => 4, 'unit' => 'SMA', 'start' => '07:30:00', 'end' => '11:30:00', 'subject' => 'Pemrograman Web & Desain Grafis SMA', 'notes' => 'Lab Multimedia SMA'],

                // JUMAT
                ['day' => 5, 'unit' => 'SD', 'start' => '07:30:00', 'end' => '10:00:00', 'subject' => 'Ekskul Komputer & Robotik SD', 'notes' => 'Lab Komputer SD'],
            ];

            foreach ($teachingSlotsData as $slotIdx => $slot) {
                EmployeeTeachingSlot::create([
                    'user_id'     => $guruFauzi->id,
                    'day_of_week' => $slot['day'],
                    'unit_id'     => $createdUnits[$slot['unit']]->id,
                    'start_time'  => $slot['start'],
                    'end_time'    => $slot['end'],
                    'subject'     => $slot['subject'],
                    'notes'       => $slot['notes'],
                    'order_index' => $slotIdx + 1,
                ]);
            }

            // Simpan ringkasan harian
            for ($d = 1; $d <= 7; $d++) {
                $daySlots = collect($teachingSlotsData)->where('day', $d);
                $isOff = $daySlots->isEmpty();
                $timeIn = $isOff ? null : $daySlots->first()['start'];
                $timeOut = $isOff ? null : $daySlots->last()['end'];
                $firstUnit = $isOff ? null : $createdUnits[$daySlots->first()['unit']]->id;

                EmployeeWorkSchedule::updateOrCreate(
                    [
                        'user_id'     => $guruFauzi->id,
                        'day_of_week' => $d,
                    ],
                    [
                        'day_name'               => $dayNames[$d],
                        'unit_id'                => $firstUnit,
                        'schedule_type'          => $isOff ? 'off' : 'custom',
                        'time_in'                => $timeIn,
                        'time_out'               => $timeOut,
                        'late_tolerance_minutes' => 15,
                        'is_day_off'             => $isOff,
                        'notes'                  => $isOff ? 'Libur' : $daySlots->pluck('subject')->implode(', '),
                    ]
                );
            }
        }

        // c. Buat contoh User Baru hasil Import Excel yang belum di-set jam kerjanya (Memicu Notifikasi Badge Task 2)
        $userRole = Role::where('name', 'user')->first();

        $unassignedUser = User::updateOrCreate(
            ['email' => 'bayu.it@absensi.com'],
            [
                'role_id' => $userRole?->id,
                'name' => 'Bayu Wicaksono, S.Kom',
                'username' => 'bayuwicaksono',
                'nip' => 'IT-2026-009',
                'password' => Hash::make('password'),
                'phone' => '081299887766',
                'position' => 'Guru IT & Multimedia',
                'department' => 'Tenaga Pendidik',
                'is_active' => true,
            ]
        );

        // Pastikan relasi jam kerja kosong untuk Bayu agar menjadi contoh nyata pegawai baru yang perlu di-set
        $unassignedUser->units()->detach();
        EmployeeWorkSchedule::where('user_id', $unassignedUser->id)->delete();
        EmployeeTeachingSlot::where('user_id', $unassignedUser->id)->delete();
    }
}
