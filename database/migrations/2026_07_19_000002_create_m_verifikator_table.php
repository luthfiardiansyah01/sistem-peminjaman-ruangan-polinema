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
        Schema::create('m_verifikator', function (Blueprint $table) {
            $table->id('verifikator_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organisasi_id')->nullable();
            $table->enum('posisi_approval', ['Ketua Umum', 'DPK', 'Presiden BEM', 'Ketua Jurusan', 'Wakil Direktur II']);
            $table->integer('urutan_approval');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('m_user')->onDelete('cascade');
            $table->foreign('organisasi_id')->references('organisasi_id')->on('m_organisasi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_verifikator');
    }
};
