<?php

namespace Database\Seeders;

use App\Models\TeachingRate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class TeachingRateSeeder extends Seeder
{
    /**
     * Jalankan seeder master tarif honor mengajar per jenjang dan mapel.
     */
    public function run(): void
    {
        $units = Unit::all()->keyBy('code');

        $rates = [
            // Jenjang TK / PAUD
            [
                'unit_code' => 'TK',
                'subject_name' => 'DEFAULT',
                'rate_per_hour' => 30000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif honor standar mengajar TK / PAUD',
                'is_active' => true,
            ],
            [
                'unit_code' => 'TK',
                'subject_name' => 'Sentra Seni & Kreativitas',
                'rate_per_hour' => 35000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif khusus kegiatan sentra seni & motorik',
                'is_active' => true,
            ],

            // Jenjang SD
            [
                'unit_code' => 'SD',
                'subject_name' => 'DEFAULT',
                'rate_per_hour' => 35000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif honor standar mengajar Sekolah Dasar (SD)',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SD',
                'subject_name' => 'IT / Komputer',
                'rate_per_hour' => 40000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif khusus pengajar lab komputer SD',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SD',
                'subject_name' => 'Bahasa Inggris',
                'rate_per_hour' => 40000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif khusus mapel muatan lokal bahasa Inggris SD',
                'is_active' => true,
            ],

            // Jenjang SMP
            [
                'unit_code' => 'SMP',
                'subject_name' => 'DEFAULT',
                'rate_per_hour' => 45000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif honor standar mengajar SMP',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SMP',
                'subject_name' => 'Informatika',
                'rate_per_hour' => 50000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif khusus mapel Informatika & Pemrograman SMP',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SMP',
                'subject_name' => 'Matematika',
                'rate_per_hour' => 48000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif pembinaan olimpiade / materi matematika',
                'is_active' => true,
            ],

            // Jenjang SMA
            [
                'unit_code' => 'SMA',
                'subject_name' => 'DEFAULT',
                'rate_per_hour' => 55000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif honor standar mengajar SMA',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SMA',
                'subject_name' => 'Informatika / Coding',
                'rate_per_hour' => 75000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif khusus coding, website, dan rekayasa perangkat lunak',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SMA',
                'subject_name' => 'Bahasa Inggris / TOEFL',
                'rate_per_hour' => 65000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif persiapan kelas internasional & TOEFL',
                'is_active' => true,
            ],
            [
                'unit_code' => 'SMA',
                'subject_name' => 'Matematika Peminatan',
                'rate_per_hour' => 60000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif mapel matematika peminatan & kalkulus',
                'is_active' => true,
            ],

            // Unit Yayasan
            [
                'unit_code' => 'YAYASAN',
                'subject_name' => 'DEFAULT',
                'rate_per_hour' => 50000,
                'rate_type' => 'per_hour',
                'notes' => 'Tarif honor kegiatan yayasan / pembinaan staf',
                'is_active' => true,
            ],
        ];

        foreach ($rates as $r) {
            $unit = $units->get($r['unit_code']);
            if (!$unit) continue;

            TeachingRate::updateOrCreate(
                [
                    'unit_id' => $unit->id,
                    'subject_name' => $r['subject_name'],
                ],
                [
                    'rate_per_hour' => $r['rate_per_hour'],
                    'rate_type' => $r['rate_type'],
                    'notes' => $r['notes'],
                    'is_active' => $r['is_active'],
                ]
            );
        }
    }
}
