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
        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->unsignedBigInteger('organisasi_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('formulir_id')->nullable()->after('organisasi_id');
            $table->string('nomor_surat', 100)->nullable()->after('pengajuan_status');

            $table->foreign('organisasi_id')->references('organisasi_id')->on('m_organisasi')->onDelete('set null');
            $table->foreign('formulir_id')->references('formulir_id')->on('m_formulir')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_pengajuan', function (Blueprint $table) {
            $table->dropForeign(['organisasi_id']);
            $table->dropForeign(['formulir_id']);
            $table->dropColumn(['organisasi_id', 'formulir_id', 'nomor_surat']);
        });
    }
};
