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
        Schema::create('t_pengajuan_approval', function (Blueprint $table) {
            $table->id('approval_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->unsignedBigInteger('verifikator_id');
            $table->integer('urutan_tahap');
            $table->enum('status_approval', ['Menunggu', 'Disetujui', 'Ditolak', 'Auto Reject'])->default('Menunggu');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('batas_waktu')->nullable();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('pengajuan_id')->on('t_pengajuan')->onDelete('cascade');
            $table->foreign('verifikator_id')->references('verifikator_id')->on('m_verifikator')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_pengajuan_approval');
    }
};
