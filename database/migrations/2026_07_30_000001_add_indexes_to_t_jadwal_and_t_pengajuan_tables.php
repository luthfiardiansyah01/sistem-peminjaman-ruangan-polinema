<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->index('jadwal_tgl');
        });

        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->index('pengajuan_tgl');
        });
    }

    public function down(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->dropIndex(['jadwal_tgl']);
        });

        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->dropIndex(['pengajuan_tgl']);
        });
    }
};
