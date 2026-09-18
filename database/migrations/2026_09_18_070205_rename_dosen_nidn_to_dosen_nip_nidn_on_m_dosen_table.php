<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi ke-2 poin 3 dari klien: field identitas dosen pada m_dosen sebelumnya hanya
 * menyimpan NIDN (dosen_nidn). Klien meminta field ini bisa menyimpan salah satu dari
 * NIP atau NIDN, jadi kolomnya di-rename (bukan tambah kolom baru) supaya data lama tetap
 * terpakai apa adanya — nilainya sendiri tidak berubah, cuma nama kolomnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->renameColumn('dosen_nidn', 'dosen_nip_nidn');
        });
    }

    public function down(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->renameColumn('dosen_nip_nidn', 'dosen_nidn');
        });
    }
};
