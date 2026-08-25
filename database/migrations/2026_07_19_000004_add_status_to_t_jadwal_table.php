<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->enum('jadwal_status', ['Akan Datang', 'Berlangsung', 'Ditinjau', 'Dokumentasi Tidak Sesuai', 'Selesai'])->default('Akan Datang')->after('jadwal_jumPes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->dropColumn('jadwal_status');
        });
    }
};
