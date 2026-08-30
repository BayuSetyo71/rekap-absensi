<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel roles.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh sistem tanpa batasan otorisasi.',
                'is_system' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Akses administrasi sistem dan manajemen hak akses pengguna.',
                'is_system' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Karyawan / Pegawai',
                'description' => 'Akses standar pengguna untuk fitur harian dan presensi.',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
