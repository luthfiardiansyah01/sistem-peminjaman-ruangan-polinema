<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perlu unique(user_id, posisi_approval) karena baris "Ketua Pelaksana" (poin 1)
 * dibuat on-the-fly (find-or-create) tiap kali pengajuan baru memilih orang yang
 * belum pernah jadi Ketua Pelaksana — tanpa constraint ini, dua submission nyaris
 * bersamaan yang memilih orang sama bisa membuat baris duplikat (race condition).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_jabatan_approval', function (Blueprint $table) {
            $table->unique(['user_id', 'posisi_approval']);
        });
    }

    public function down(): void
    {
        Schema::table('m_jabatan_approval', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'posisi_approval']);
        });
    }
};
