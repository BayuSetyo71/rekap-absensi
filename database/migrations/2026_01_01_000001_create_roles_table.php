<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk tabel roles.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // contoh: superadmin, admin, user
            $table->string('display_name'); // contoh: Super Admin, Administrator, Pengguna
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // role bawaan sistem tidak boleh dihapus sembarangan
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
