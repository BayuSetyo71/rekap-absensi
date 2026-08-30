<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk tabel menus.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->string('code')->unique(); // contoh: 'dashboard', 'menus', 'roles', 'users'
            $table->string('name'); // Label menu: 'Dashboard', 'Manajemen Menu', dll
            $table->string('route_name')->nullable(); // Nama route Laravel: 'admin.menus.index'
            $table->string('url')->nullable(); // URL path fallback jika tidak pakai route_name
            $table->string('icon')->default('bi bi-circle'); // Icon bootstrap / fontawesome
            $table->integer('order_index')->default(0); // Urutan tampil di sidebar
            $table->boolean('is_active')->default(true); // Status aktif / non-aktif
            $table->boolean('has_create')->default(true); // Mendukung izin Create
            $table->boolean('has_update')->default(true); // Mendukung izin Update
            $table->boolean('has_delete')->default(true); // Mendukung izin Delete
            $table->boolean('has_export')->default(false); // Mendukung izin Export
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
