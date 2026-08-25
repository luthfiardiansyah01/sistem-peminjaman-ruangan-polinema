<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot keanggotaan mahasiswa di organisasi (mis. BEM, HMJ). Satu mahasiswa
 * bisa jadi anggota lebih dari satu organisasi sekaligus. Menempel ke
 * mahasiswa_id (bukan user_id) karena ini atribut mahasiswa, konsisten
 * dengan prodi_id/kelas_id yang juga menempel di m_mahasiswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_mahasiswa_organisasi', function (Blueprint $table) {
            $table->id('mahasiswa_organisasi_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('organisasi_id');
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('m_mahasiswa')->onDelete('cascade');
            $table->foreign('organisasi_id')->references('organisasi_id')->on('m_organisasi')->onDelete('cascade');
            $table->unique(['mahasiswa_id', 'organisasi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_mahasiswa_organisasi');
    }
};
