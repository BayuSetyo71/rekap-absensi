<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel master unit dan jam kerja pegawai.
     */
    public function up(): void
    {
        // 1. Tabel Unit / Jenjang Pendidikan di Yayasan (TK, SD, SMP, SMA, Yayasan)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // TK, SD, SMP, SMA, YAYASAN
            $table->string('name', 100); // contoh: TK / PAUD, SD Islam Terpadu, dll.
            $table->string('color', 20)->default('#4f46e5'); // Kode warna tema badge
            $table->time('default_time_in')->default('07:00:00'); // Default jam masuk
            $table->time('default_time_out')->default('14:00:00'); // Default jam pulang
            $table->unsignedSmallInteger('default_late_tolerance')->default(15); // Toleransi keterlambatan (menit)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Jadwal Jam Kerja Standar per Unit (Senin - Minggu)
        Schema::create('unit_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 7 = Minggu (ISO-8601)
            $table->string('day_name', 20); // 'Senin', 'Selasa', dll.
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->boolean('is_day_off')->default(false); // True jika hari libur (misal Sabtu/Minggu)
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['unit_id', 'day_of_week'], 'unit_day_unique');
        });

        // 3. Tabel Pivot Hubungan Pegawai/Guru dengan Unit yang Diajar (Multi-Unit)
        Schema::create('employee_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Apakah unit homebase utama
            $table->timestamps();

            $table->unique(['user_id', 'unit_id'], 'employee_unit_unique');
        });

        // 4. Tabel Pengaturan Jam Kerja Harian Spesifik Pegawai / Guru (Senin - Minggu)
        Schema::create('employee_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 7 = Minggu
            $table->string('day_name', 20); // 'Senin', 'Selasa', dll.
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null'); // Unit bertugas pada hari tersebut
            $table->string('schedule_type', 30)->default('default_unit'); // 'default_unit', 'custom', 'off'
            $table->time('time_in')->nullable(); // Jam masuk spesifik
            $table->time('time_out')->nullable(); // Jam pulang spesifik
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->boolean('is_day_off')->default(false); // True jika hari libur
            $table->string('notes', 255)->nullable(); // Catatan jadwal harian
            $table->timestamps();

            $table->unique(['user_id', 'day_of_week'], 'employee_day_unique');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_work_schedules');
        Schema::dropIfExists('employee_units');
        Schema::dropIfExists('unit_work_schedules');
        Schema::dropIfExists('units');
    }
};
