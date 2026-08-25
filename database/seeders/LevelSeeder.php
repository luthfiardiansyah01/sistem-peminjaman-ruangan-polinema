<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Level VRF (verifikator sebagai jenis akun terpisah) sudah dihapus — jabatan approval
        // sekarang menempel ke akun DSN/MHS yang sudah ada (lihat m_jabatan_approval,
        // JabatanApprovalSeeder). Baris level_id=5 lama di DB existing dibiarkan tidak terpakai.
        $data = [
            ['level_id' => 1, 'level_kode' => 'ADM', 'level_nama' => 'Admin'],
            ['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen'],
            ['level_id' => 3, 'level_kode' => 'TDK', 'level_nama' => 'Tendik'],
            ['level_id' => 4, 'level_kode' => 'MHS', 'level_nama' => 'Mahasiswa'],
        ];

        // updateOrInsert per baris agar aman dijalankan ulang di database yang sudah terisi (idempotent)
        foreach ($data as $row) {
            DB::table('m_level')->updateOrInsert(['level_id' => $row['level_id']], $row);
        }
    }
}
