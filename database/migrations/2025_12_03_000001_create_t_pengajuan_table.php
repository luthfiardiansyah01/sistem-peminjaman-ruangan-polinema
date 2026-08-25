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
        Schema::create('t_pengajuan', function (Blueprint $table) {
            $table->id('pengajuan_id');
            $table->unsignedBigInteger('user_id');
            $table->string('pengajuan_nama', 255);
            $table->date('pengajuan_tgl');
            $table->time('pengajuan_jam_mulai');
            $table->time('pengajuan_jam_selesai');
            $table->integer('pengajuan_jumPes');
            $table->text('pengajuan_keterangan')->nullable();
            $table->enum('pengajuan_status', ['Diajukan', 'Diterima', 'Ditolak'])->default('Diajukan');
            $table->text('catatan_verifikator')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('m_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_pengajuan');
    }
};
