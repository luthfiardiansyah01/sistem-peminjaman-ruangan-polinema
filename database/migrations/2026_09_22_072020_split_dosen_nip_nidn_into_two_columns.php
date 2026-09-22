<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi ke-2 poin 3/4 (klarifikasi lanjutan): klien konfirmasi ada dosen yang punya NIP
 * DAN NIDN sekaligus, dengan NIP diprioritaskan tampil di surat. Satu field gabungan
 * (dosen_nip_nidn, hasil migration sebelumnya) tidak cukup untuk kasus ini — dipecah jadi
 * dua kolom nullable terpisah. Data lama dipindahkan berdasarkan panjang digit yang sudah
 * dikonfirmasi klien: NIDN 10 digit, NIP 18 digit; nilai lain (longgar dari validasi lama)
 * dianggap NIDN legacy supaya tidak hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->string('dosen_nip', 18)->nullable()->after('dosen_nip_nidn');
            $table->string('dosen_nidn', 10)->nullable()->after('dosen_nip_nidn');
        });

        DB::table('m_dosen')->orderBy('dosen_id')->each(function ($row) {
            $value = (string) $row->dosen_nip_nidn;
            $update = strlen($value) === 18
                ? ['dosen_nip' => $value]
                : ['dosen_nidn' => $value];

            DB::table('m_dosen')->where('dosen_id', $row->dosen_id)->update($update);
        });

        Schema::table('m_dosen', function (Blueprint $table) {
            $table->dropColumn('dosen_nip_nidn');
        });
    }

    public function down(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->string('dosen_nip_nidn', 50)->nullable()->after('dosen_nama');
        });

        DB::table('m_dosen')->orderBy('dosen_id')->each(function ($row) {
            DB::table('m_dosen')->where('dosen_id', $row->dosen_id)->update([
                'dosen_nip_nidn' => $row->dosen_nip ?: $row->dosen_nidn,
            ]);
        });

        Schema::table('m_dosen', function (Blueprint $table) {
            $table->dropColumn(['dosen_nip', 'dosen_nidn']);
        });
    }
};
