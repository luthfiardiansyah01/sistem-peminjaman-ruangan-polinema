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
        Schema::table('m_ruangan', function (Blueprint $table) {
            $table->enum('ruangan_status', ['Tersedia', 'Diajukan', 'Tidak Tersedia'])->default('Tersedia')->after('ruangan_kuota');
            $table->enum('ruangan_kategori', ['Jurusan', 'Umum'])->default('Jurusan')->after('ruangan_status');
            $table->string('ruangan_foto', 255)->nullable()->after('ruangan_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_ruangan', function (Blueprint $table) {
            $table->dropColumn(['ruangan_status', 'ruangan_kategori', 'ruangan_foto']);
        });
    }
};
