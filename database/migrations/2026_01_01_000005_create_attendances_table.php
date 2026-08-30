<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel data absensi/presensi.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('attendance_date'); // Tanggal absensi (YYYY-MM-DD)
            $table->time('check_in')->nullable(); // Jam masuk (HH:MM:SS)
            $table->time('check_out')->nullable(); // Jam pulang (HH:MM:SS)
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->text('notes')->nullable(); // Keterangan / alasan / catatan inject
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User peng-inject
            $table->timestamps();

            // Cegah duplikasi absensi tanggal yang sama untuk pegawai yang sama
            $table->unique(['user_id', 'attendance_date'], 'user_attendance_date_unique');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
