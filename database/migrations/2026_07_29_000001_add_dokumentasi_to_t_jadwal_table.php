<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->string('foto_penggunaan')->nullable()->after('jadwal_status');
            $table->string('foto_kebersihan')->nullable()->after('foto_penggunaan');
            $table->string('foto_kunci')->nullable()->after('foto_kebersihan');
        });
    }

    public function down(): void
    {
        Schema::table('t_jadwal', function (Blueprint $table) {
            $table->dropColumn(['foto_penggunaan', 'foto_kebersihan', 'foto_kunci']);
        });
    }
};
