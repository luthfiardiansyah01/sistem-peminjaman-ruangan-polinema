<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'organisasi_kode' => 'HMJ',
                'organisasi_nama' => 'Himpunan Mahasiswa Jurusan',
                'organisasi_logo' => null,
            ],
            [
                'organisasi_kode' => 'BEM',
                'organisasi_nama' => 'Badan Eksekutif Mahasiswa',
                'organisasi_logo' => null,
            ],
        ];

        // updateOrInsert per kode agar aman dijalankan ulang (idempotent)
        foreach ($data as $row) {
            DB::table('m_organisasi')->updateOrInsert(['organisasi_kode' => $row['organisasi_kode']], $row);
        }
    }
}
