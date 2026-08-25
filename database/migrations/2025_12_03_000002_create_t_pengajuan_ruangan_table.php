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
        Schema::create('t_pengajuan_ruangan', function (Blueprint $table) {
            $table->id('pengajuan_ruangan_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->unsignedBigInteger('ruangan_id');
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('pengajuan_id')->on('t_pengajuan')->onDelete('cascade');
            $table->foreign('ruangan_id')->references('ruangan_id')->on('m_ruangan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_pengajuan_ruangan');
    }
};
