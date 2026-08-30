<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk tabel role_menu_permissions.
     */
    public function up(): void
    {
        Schema::create('role_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->boolean('can_view')->default(false); // Izin melihat menu / index data
            $table->boolean('can_create')->default(false); // Izin menambah data (create/store)
            $table->boolean('can_update')->default(false); // Izin mengubah data (edit/update)
            $table->boolean('can_delete')->default(false); // Izin menghapus data (destroy)
            $table->boolean('can_export')->default(false); // Izin mengunduh/export data
            $table->timestamps();

            $table->unique(['role_id', 'menu_id'], 'role_menu_unique');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_permissions');
    }
};
