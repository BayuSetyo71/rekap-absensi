<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuPermission;
use Illuminate\Database\Seeder;

class RoleMenuPermissionSeeder extends Seeder
{
    /**
     * Jalankan seeder matriks hak akses menu per role.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $menus = Menu::all();

        if ($adminRole) {
            foreach ($menus as $menu) {
                RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $adminRole->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'can_view' => true,
                        'can_create' => $menu->has_create,
                        'can_update' => $menu->has_update,
                        'can_delete' => $menu->has_delete,
                        'can_export' => $menu->has_export,
                    ]
                );
            }
        }

        if ($userRole) {
            foreach ($menus as $menu) {
                // User role (Karyawan / Guru) fokus pada modul personal: Dashboard, Jadwal Mengajar Saya, Informasi Jadwal Guru, Riwayat Absensi, Rekap Pribadi, dan Slip Gaji Pribadi
                $canView = in_array($menu->code, [
                    'dashboard',
                    'schedule-group',
                    'my-schedule',
                    'schedule-info',
                    'attendance-group',
                    'attendances',
                    'attendance-recap',
                    'payroll-group',
                    'payrolls',
                ]);
                $canExport = in_array($menu->code, ['my-schedule', 'schedule-info', 'attendances', 'attendance-recap', 'payrolls']);
                RoleMenuPermission::updateOrCreate(
                    [
                        'role_id' => $userRole->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'can_view' => $canView,
                        'can_create' => false,
                        'can_update' => false,
                        'can_delete' => false,
                        'can_export' => $canExport,
                    ]
                );
            }
        }
    }
}
