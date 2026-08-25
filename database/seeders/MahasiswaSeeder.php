<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'mahasiswa_id' => 1,
                'user_id'   => 4,
                'prodi_id'  => 1,
                'kelas_id' => 1,
                'mahasiswa_nama'  => 'Mahasiswa 1',
                'mahasiswa_nim'  => '2241760001',
                'mahasiswa_noHp'  => '081333333333',
            ],
            [
                'mahasiswa_id' => 2,
                'user_id'   => 6,
                'prodi_id'  => 2,
                'kelas_id' => 5,
                'mahasiswa_nama'  => 'Ratih Paramita',
                'mahasiswa_nim'  => '2241760063',
                'mahasiswa_noHp'  => '082234293322',
            ],
            [
                'mahasiswa_id' => 3,
                'user_id'   => 10,
                'prodi_id'  => 1,
                'kelas_id' => 1,
                'mahasiswa_nama'  => 'Ketua Umum HMJ',
                'mahasiswa_nim'  => '2241760003',
                'mahasiswa_noHp'  => '082234293323',
            ],
            [
                'mahasiswa_id' => 4,
                'user_id'   => 11,
                'prodi_id'  => 1,
                'kelas_id' => 1,
                'mahasiswa_nama'  => 'Presiden BEM',
                'mahasiswa_nim'  => '2241760010',
                'mahasiswa_noHp'  => '082234293324',
            ],
        ];

        DB::table('m_mahasiswa')->insert($data);
    }
}
