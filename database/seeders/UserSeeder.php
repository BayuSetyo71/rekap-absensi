<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder akun pengguna.
     */
    public function run(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'role_id' => $superadminRole?->id,
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'nip' => 'SA-001',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'position' => 'IT System Administrator',
                'department' => 'Information Technology',
                'is_active' => true,
            ]
        );

        // 2. Administrator
        User::updateOrCreate(
            ['email' => 'admin2@absensi.com'],
            [
                'role_id' => $adminRole?->id,
                'name' => 'Admin HRD',
                'username' => 'adminhrd',
                'nip' => 'HR-002',
                'password' => Hash::make('password'),
                'phone' => '081298765432',
                'position' => 'HR Operations Staff',
                'department' => 'Human Resources',
                'is_active' => true,
            ]
        );

        // 3. User / Karyawan Demo
        User::updateOrCreate(
            ['email' => 'user@absensi.com'],
            [
                'role_id' => $userRole?->id,
                'name' => 'Ahmad Fauzi',
                'username' => 'ahmadfauzi',
                'nip' => 'KY-1001',
                'password' => Hash::make('password'),
                'phone' => '085712345678',
                'position' => 'Staff Operasional',
                'department' => 'Operasional',
                'is_active' => true,
            ]
        );
    }
}
