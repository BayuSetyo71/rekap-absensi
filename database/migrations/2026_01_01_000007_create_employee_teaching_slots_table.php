<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel sesi / slot mengajar harian guru.
     */
    public function up(): void
    {
        Schema::create('employee_teaching_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 7 = Minggu
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null'); // TK, SD, SMP, SMA, Yayasan
            $table->time('start_time'); // Contoh: 07:30:00
            $table->time('end_time');   // Contoh: 08:30:00
            $table->string('subject', 100)->nullable(); // Contoh: 'IT / Komputer', 'Bahasa Inggris', dll.
            $table->string('notes', 255)->nullable();   // Catatan kelas / ruangan
            $table->unsignedSmallInteger('order_index')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week'], 'user_day_slots_idx');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_teaching_slots');
    }
};
