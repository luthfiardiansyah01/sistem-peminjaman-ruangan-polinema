<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel konfigurasi global untuk fitur Periode (Open/Closed) — gerbang seluruh
 * transactional operation (pengajuan peminjaman, approval berjenjang, jadwal),
 * sekaligus mencatat tahun ajaran/semester & rentang tanggal periode tersebut.
 * Single-row config table (bukan master data per-baris) — lihat PeriodeService::current(),
 * yang selalu mengambil/membuat SATU baris saja (firstOrCreate).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_periode', function (Blueprint $table) {
            $table->id('periode_id');
            $table->string('tahun_ajaran', 9);      // 2025/2026
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->enum('periode_status', ['Open', 'Closed'])
                  ->default('Open');

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('m_user', 'user_id')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_periode');
    }
};
