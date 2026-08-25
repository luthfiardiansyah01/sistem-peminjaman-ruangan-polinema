<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REQ 2 — Multi-foto Ruangan.
     * Ubah kolom ruangan_foto dari varchar (string) menjadi text agar dapat menampung
     * JSON array path foto yang lebih panjang dari 255 karakter.
     * Data lama (plain string path) tetap valid karena accessor getFotosAttribute()
     * di RuanganModel menangani kedua format secara backward-compatible.
     */
    public function up(): void
    {
        Schema::table('m_ruangan', function (Blueprint $table) {
            $table->text('ruangan_foto')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('m_ruangan', function (Blueprint $table) {
            $table->string('ruangan_foto', 255)->nullable()->change();
        });
    }
};
