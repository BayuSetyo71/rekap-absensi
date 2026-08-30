<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi penambahan kolom pada tabel users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->onDelete('set null');
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('nip', 50)->nullable()->unique()->after('username');
            $table->string('phone', 25)->nullable()->after('email');
            $table->string('position', 100)->nullable()->after('phone'); // Jabatan
            $table->string('department', 100)->nullable()->after('position'); // Divisi/Departemen
            $table->string('avatar')->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'role_id',
                'username',
                'nip',
                'phone',
                'position',
                'department',
                'avatar',
                'is_active',
            ]);
        });
    }
};
