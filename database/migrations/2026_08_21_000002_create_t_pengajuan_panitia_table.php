<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daftar panitia/peserta per pengajuan, dipilih dari anggota organisasi yang
 * sama dengan organisasi_id pengajuan. keterangan sengaja isian bebas
 * (bukan select) karena peran panitia sangat bervariasi per acara.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_pengajuan_panitia', function (Blueprint $table) {
            $table->id('panitia_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->unsignedBigInteger('user_id');
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('pengajuan_id')->on('t_pengajuan')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('m_user')->onDelete('cascade');
            $table->unique(['pengajuan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_pengajuan_panitia');
    }
};
