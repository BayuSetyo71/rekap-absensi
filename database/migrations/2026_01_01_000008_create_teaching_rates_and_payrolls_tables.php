<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel master tarif honor mengajar dan transaksi payroll.
     */
    public function up(): void
    {
        // 1. Tabel Master Tarif Honor Mengajar per Unit & Mapel
        Schema::create('teaching_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->string('subject_name', 150)->default('DEFAULT'); // 'DEFAULT' atau nama mapel spesifik
            $table->decimal('rate_per_hour', 12, 2)->default(0); // Tarif per 60 menit (Rp)
            $table->string('rate_type', 30)->default('per_hour'); // 'per_hour' atau 'per_session'
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['unit_id', 'subject_name', 'is_active'], 'unit_subject_rate_idx');
        });

        // 2. Tabel Rekapitulasi Penggajian Bulanan Pegawai (Header)
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('period_month', 7); // Format: 'YYYY-MM', contoh '2026-08'
            $table->unsignedInteger('total_present_days')->default(0);
            $table->unsignedInteger('total_sessions_taught')->default(0);
            $table->decimal('total_hours_taught', 8, 2)->default(0); // Jam desimal, misal 22.50 jam
            $table->decimal('gross_teaching_amount', 15, 2)->default(0); // Total kotor honor mengajar
            $table->decimal('total_allowances', 15, 2)->default(0); // Total penambahan/tunjangan
            $table->decimal('total_deductions', 15, 2)->default(0); // Total potongan
            $table->decimal('net_salary', 15, 2)->default(0); // Take home pay
            $table->enum('status', ['draft', 'locked', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'period_month'], 'user_period_payroll_unique');
            $table->index(['period_month', 'status'], 'period_status_idx');
        });

        // 3. Tabel Rincian Breakdown Sesi Mengajar per Unit & Mapel (Detail)
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->string('subject', 150);
            $table->unsignedInteger('total_sessions')->default(0);
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('rate_applied', 12, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index('payroll_id');
        });

        // 4. Tabel Penyesuaian Gaji (Tunjangan Tambahan & Potongan)
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->enum('type', ['allowance', 'deduction']); // 'allowance' = tunjangan, 'deduction' = potongan
            $table->string('name', 150); // Contoh: 'Tunjangan Wali Kelas', 'Kasbon Koperasi'
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('payroll_id');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
        Schema::dropIfExists('payroll_details');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('teaching_rates');
    }
};
