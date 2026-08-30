<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel menus dengan struktur pengelompokan yang rapi.
     */
    public function run(): void
    {
        // 1. Menu Beranda / Portal Menu (Single Top-Level Launcher)
        $dashboard = Menu::updateOrCreate(
            ['code' => 'dashboard'],
            [
                'parent_id' => null,
                'name' => 'Portal Menu',
                'route_name' => 'dashboard',
                'url' => '/dashboard',
                'icon' => 'bi bi-grid-fill',
                'order_index' => 1,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => false,
            ]
        );

        // 2. KELOMPOK: Jadwal & Jam Kerja (Parent)
        $scheduleGroup = Menu::updateOrCreate(
            ['code' => 'schedule-group'],
            [
                'parent_id' => null,
                'name' => 'Jadwal & Jam Kerja',
                'route_name' => null,
                'url' => null,
                'icon' => 'bi bi-calendar2-range-fill',
                'order_index' => 2,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => false,
            ]
        );

        // 2a. Sub-menu: Jadwal Mengajar Saya (Perorangan)
        Menu::updateOrCreate(
            ['code' => 'my-schedule'],
            [
                'parent_id' => $scheduleGroup->id,
                'name' => 'Jadwal Mengajar Saya',
                'route_name' => 'my-schedule.index',
                'url' => '/my-schedule',
                'icon' => 'bi bi-calendar2-week-fill',
                'order_index' => 1,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => true,
            ]
        );

        // 2b. Sub-menu: Informasi Jadwal Mengajar Yayasan (Seluruh Guru)
        Menu::updateOrCreate(
            ['code' => 'schedule-info'],
            [
                'parent_id' => $scheduleGroup->id,
                'name' => 'Informasi Jadwal Guru',
                'route_name' => 'schedule-info.index',
                'url' => '/schedule-info',
                'icon' => 'bi bi-calendar3-event',
                'order_index' => 2,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => true,
            ]
        );

        // 2c. Sub-menu: Pengaturan Jam Kerja (Transaksi / Konfigurasi)
        Menu::updateOrCreate(
            ['code' => 'work-schedules'],
            [
                'parent_id' => $scheduleGroup->id,
                'name' => 'Pengaturan Jam Kerja',
                'route_name' => 'work-schedules.index',
                'url' => '/work-schedules',
                'icon' => 'bi bi-sliders2-vertical',
                'order_index' => 3,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => true,
            ]
        );

        // 3. KELOMPOK: Presensi & Absensi (Parent)
        $attendanceGroup = Menu::updateOrCreate(
            ['code' => 'attendance-group'],
            [
                'parent_id' => null,
                'name' => 'Presensi & Absensi',
                'route_name' => null,
                'url' => null,
                'icon' => 'bi bi-calendar2-check-fill',
                'order_index' => 3,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => false,
            ]
        );

        // 3a. Sub-menu: Data Absensi & Inject Excel
        Menu::updateOrCreate(
            ['code' => 'attendances'],
            [
                'parent_id' => $attendanceGroup->id,
                'name' => 'Data Absensi & Inject Excel',
                'route_name' => 'attendances.index',
                'url' => '/attendances',
                'icon' => 'bi bi-file-earmark-spreadsheet',
                'order_index' => 1,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => true,
            ]
        );

        // 3b. Sub-menu: Rekap Absen Pegawai
        Menu::updateOrCreate(
            ['code' => 'attendance-recap'],
            [
                'parent_id' => $attendanceGroup->id,
                'name' => 'Rekap Absen Pegawai',
                'route_name' => 'attendance-recap.index',
                'url' => '/attendance-recap',
                'icon' => 'bi bi-person-lines-fill',
                'order_index' => 2,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => true,
            ]
        );

        // 3c. Sub-menu: Laporan Presensi (Export Excel & PDF)
        Menu::updateOrCreate(
            ['code' => 'reports'],
            [
                'parent_id' => $attendanceGroup->id,
                'name' => 'Laporan Presensi',
                'route_name' => 'reports.index',
                'url' => '/reports',
                'icon' => 'bi bi-file-earmark-bar-graph-fill',
                'order_index' => 3,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => true,
            ]
        );

        // 4. KELOMPOK: Penggajian & Honor (Parent)
        $payrollGroup = Menu::updateOrCreate(
            ['code' => 'payroll-group'],
            [
                'parent_id' => null,
                'name' => 'Penggajian & Honor',
                'route_name' => null,
                'url' => null,
                'icon' => 'bi bi-cash-stack',
                'order_index' => 4,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => false,
            ]
        );

        // 4a. Sub-menu: Tarif Honor Mengajar
        Menu::updateOrCreate(
            ['code' => 'teaching-rates'],
            [
                'parent_id' => $payrollGroup->id,
                'name' => 'Tarif Honor Mengajar',
                'route_name' => 'teaching-rates.index',
                'url' => '/teaching-rates',
                'icon' => 'bi bi-tags-fill',
                'order_index' => 1,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => false,
            ]
        );

        // 4b. Sub-menu: Penggajian Guru (Payroll)
        Menu::updateOrCreate(
            ['code' => 'payrolls'],
            [
                'parent_id' => $payrollGroup->id,
                'name' => 'Penggajian Guru (Payroll)',
                'route_name' => 'payrolls.index',
                'url' => '/payrolls',
                'icon' => 'bi bi-wallet2',
                'order_index' => 2,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => true,
            ]
        );

        // 5. KELOMPOK: Master & Pengaturan Sistem (Parent)
        $settingsGroup = Menu::updateOrCreate(
            ['code' => 'settings-group'],
            [
                'parent_id' => null,
                'name' => 'Master & Pengaturan',
                'route_name' => null,
                'url' => null,
                'icon' => 'bi bi-gear-fill',
                'order_index' => 5,
                'is_active' => true,
                'has_create' => false,
                'has_update' => false,
                'has_delete' => false,
                'has_export' => false,
            ]
        );

        // 4a. Sub-menu: Manajemen Pengguna
        Menu::updateOrCreate(
            ['code' => 'users'],
            [
                'parent_id' => $settingsGroup->id,
                'name' => 'Manajemen Pengguna',
                'route_name' => 'users.index',
                'url' => '/users',
                'icon' => 'bi bi-people-fill',
                'order_index' => 1,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => true,
            ]
        );

        // 4b. Sub-menu: Manajemen Role & Izin
        Menu::updateOrCreate(
            ['code' => 'roles'],
            [
                'parent_id' => $settingsGroup->id,
                'name' => 'Manajemen Role & Izin',
                'route_name' => 'roles.index',
                'url' => '/roles',
                'icon' => 'bi bi-shield-lock-fill',
                'order_index' => 2,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => false,
            ]
        );

        // 4c. Sub-menu: Manajemen Menu
        Menu::updateOrCreate(
            ['code' => 'menus'],
            [
                'parent_id' => $settingsGroup->id,
                'name' => 'Manajemen Menu',
                'route_name' => 'menus.index',
                'url' => '/menus',
                'icon' => 'bi bi-menu-button-wide-fill',
                'order_index' => 3,
                'is_active' => true,
                'has_create' => true,
                'has_update' => true,
                'has_delete' => true,
                'has_export' => false,
            ]
        );
    }
}
