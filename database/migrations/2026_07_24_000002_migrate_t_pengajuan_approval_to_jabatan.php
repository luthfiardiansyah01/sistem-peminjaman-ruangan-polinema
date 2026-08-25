<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * t_pengajuan_approval.verifikator_id -> jabatan_id, FK diarahkan ke
 * m_jabatan_approval (lihat 2026_07_24_000001_create_m_jabatan_approval_table.php).
 *
 * Baris t_pengajuan_approval yang sudah ada menunjuk ke m_verifikator (dihapus di
 * migration berikutnya) yang tidak punya padanan di m_jabatan_approval (baru dibuat,
 * masih kosong sampai seeder jalan) — dikosongkan dulu di sini. Data ini hanya data uji
 * (bukan data produksi nyata), jadi aman dibuang; pengajuan terkait otomatis kembali ke
 * kondisi "belum ada tahap approval" (akan dibuat ulang oleh pengajuan baru berikutnya).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('t_pengajuan_approval')->delete();

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('t_pengajuan_approval', function (Blueprint $table) {
                $table->dropForeign(['verifikator_id']);
            });
        }

        Schema::table('t_pengajuan_approval', function (Blueprint $table) {
            $table->renameColumn('verifikator_id', 'jabatan_id');
        });

        Schema::table('t_pengajuan_approval', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('jabatan_id')->on('m_jabatan_approval')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('t_pengajuan_approval', function (Blueprint $table) {
                $table->dropForeign(['jabatan_id']);
            });
        }

        Schema::table('t_pengajuan_approval', function (Blueprint $table) {
            $table->renameColumn('jabatan_id', 'verifikator_id');
        });

        Schema::table('t_pengajuan_approval', function (Blueprint $table) {
            $table->foreign('verifikator_id')->references('verifikator_id')->on('m_verifikator')->onDelete('cascade');
        });
    }
};
