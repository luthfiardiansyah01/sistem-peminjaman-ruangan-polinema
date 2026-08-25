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
            $table->string('ruangan_kode', 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_ruangan', function (Blueprint $table) {
            $table->string('ruangan_kode', 5)->change();
        });
    }
};
