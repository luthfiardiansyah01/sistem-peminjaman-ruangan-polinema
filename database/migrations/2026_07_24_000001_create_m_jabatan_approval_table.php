<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menggantikan m_verifikator (2026_07_19_000002): jabatan approval menempel ke
 * user yang SUDAH ADA (dosen atau mahasiswa), bukan jenis akun tersendiri.
 * posisi_approval sengaja string biasa (bukan enum) supaya posisi baru bisa
 * ditambah tanpa migration lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_jabatan_approval', function (Blueprint $table) {
            $table->id('jabatan_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organisasi_id')->nullable();
            $table->string('posisi_approval', 100);
            $table->integer('urutan_approval');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('m_user')->onDelete('cascade');
            $table->foreign('organisasi_id')->references('organisasi_id')->on('m_organisasi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_jabatan_approval');
    }
};
