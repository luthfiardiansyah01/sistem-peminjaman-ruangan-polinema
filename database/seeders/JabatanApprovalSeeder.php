<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Menggantikan VerifikatorSeeder — jabatan approval dipasang ke DOSEN/MAHASISWA
 * yang SUDAH ADA di m_dosen/m_mahasiswa (bukan bikin akun baru khusus verifikator).
 * DPK & Ketua Jurusan & Wakil Direktur II = dosen; Ketua Umum & Presiden BEM = mahasiswa,
 * sesuai arahan klien.
 *
 * Password 5 akun di bawah di-reset ke '123456' (idempotent, tidak mengubah data profil
 * dosen/mahasiswa lain) supaya bisa dipakai login untuk pengujian (mis. Playwright e2e).
 * SENGAJA menghindari 'mahasiswa1'/'dosen'/'tendik1'/'admin1' — akun itu sudah dipakai
 * sebagai kredensial umum di tests/e2e/fixtures/auth.fixture.js (USERS.MHS/DSN/TDK/ADM)
 * dengan password aslinya sendiri (mis. mahasiswa1 = NIM 2241760001); me-reset itu akan
 * merusak kredensial yang sudah didokumentasikan di sana.
 */
class JabatanApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $hmjId = DB::table('m_organisasi')->where('organisasi_kode', 'HMJ')->value('organisasi_id');
        $bemId = DB::table('m_organisasi')->where('organisasi_kode', 'BEM')->value('organisasi_id');

        // Perbaikan-maju: run sebelumnya sempat salah me-reset password 'mahasiswa1'
        // (dipakai juga sebagai USERS.MHS di auth.fixture.js) — kembalikan ke NIM aslinya.
        DB::table('m_user')->where('username', 'mahasiswa1')->update(['password' => Hash::make('2241760001')]);

        // username => posisi yang dipegang orang itu. Dipilih 5 akun dosen/mahasiswa
        // BERBEDA (bukan 1 orang merangkap semua posisi) supaya pengecekan kepemilikan
        // approval (siapa boleh proses baris siapa) tetap teruji dengan jelas, dan
        // semuanya SELAIN akun umum yang sudah dipakai fixture lain.
        $assignments = [
            '2241760003' => ['organisasi_id' => $hmjId, 'posisi_approval' => 'Ketua Umum', 'urutan_approval' => 1],
            'dosen2' => ['organisasi_id' => null, 'posisi_approval' => 'DPK', 'urutan_approval' => 2],
            '2241760063' => ['organisasi_id' => $bemId, 'posisi_approval' => 'Presiden BEM', 'urutan_approval' => 2],
            '0013333333' => ['organisasi_id' => null, 'posisi_approval' => 'Ketua Jurusan', 'urutan_approval' => 3],
            '0014444444' => ['organisasi_id' => null, 'posisi_approval' => 'Wakil Direktur II', 'urutan_approval' => 3],
        ];

        foreach ($assignments as $username => $jabatan) {
            $userId = DB::table('m_user')->where('username', $username)->value('user_id');
            if (!$userId) {
                // Username tidak ditemukan (mis. seeder dosen/mahasiswa belum jalan di
                // environment ini) — lewati agar seeder lain tetap jalan.
                continue;
            }

            DB::table('m_user')->where('user_id', $userId)->update(['password' => Hash::make('123456')]);

            DB::table('m_jabatan_approval')->updateOrInsert(
                ['user_id' => $userId, 'posisi_approval' => $jabatan['posisi_approval']],
                [
                    'organisasi_id' => $jabatan['organisasi_id'],
                    'urutan_approval' => $jabatan['urutan_approval'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Baris jabatan lama untuk 'mahasiswa1' dari run sebelumnya (sebelum diganti ke
        // 2241760003) — bersihkan supaya tidak nyangkut sebagai Ketua Umum ganda.
        $mahasiswa1Id = DB::table('m_user')->where('username', 'mahasiswa1')->value('user_id');
        if ($mahasiswa1Id) {
            DB::table('m_jabatan_approval')->where('user_id', $mahasiswa1Id)->where('posisi_approval', 'Ketua Umum')->delete();
        }
    }
}
