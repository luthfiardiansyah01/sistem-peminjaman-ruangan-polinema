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
        Schema::create('m_organisasi', function (Blueprint $table) {
            $table->id('organisasi_id');
            $table->string('organisasi_kode', 10)->unique();
            $table->string('organisasi_nama', 255);
            $table->string('organisasi_logo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_organisasi');
    }
};
