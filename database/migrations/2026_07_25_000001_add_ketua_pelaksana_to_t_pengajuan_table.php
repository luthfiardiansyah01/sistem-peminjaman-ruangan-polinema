<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ketua Pelaksana (poin 1) — dipilih bebas per-pengajuan (dosen/mahasiswa manapun),
 * dijadikan tahap approval baru (urutan_tahap=0, sebelum Ketua Umum). Nullable di DB
 * (baris t_pengajuan lama belum punya nilai) meski wajib diisi di validasi untuk
 * pengajuan baru — lihat PengajuanService::rules().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->unsignedBigInteger('ketua_pelaksana_user_id')->nullable()->after('user_id');
            $table->foreign('ketua_pelaksana_user_id')->references('user_id')->on('m_user')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->dropForeign(['ketua_pelaksana_user_id']);
            $table->dropColumn('ketua_pelaksana_user_id');
        });
    }
};
