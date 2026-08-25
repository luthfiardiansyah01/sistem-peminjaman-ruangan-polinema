<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'dosen_id' => 1,
                'user_id'   => 2,
                'prodi_id'  => 1,
                'dosen_nama'  => 'Dosen 1',
                'dosen_nidn'  => '198111111111111111',
                'dosen_noHp'  => '08111111111',
            ],
            [
                'dosen_id' => 2,
                'user_id'   => 7,
                'prodi_id'  => 1,
                'dosen_nama'  => 'Dosen 2',
                'dosen_nidn'  => '198222222222222222',
                'dosen_noHp'  => '08122222222',
            ],
            [
                'dosen_id' => 3,
                'user_id'   => 8,
                'prodi_id'  => 1,
                'dosen_nama'  => 'Ketua Jurusan',
                'dosen_nidn'  => '198333333333333333',
                'dosen_noHp'  => '08133333333',
            ],
            [
                'dosen_id' => 4,
                'user_id'   => 9,
                'prodi_id'  => 1,
                'dosen_nama'  => 'Wadir 2',
                'dosen_nidn'  => '198444444444444444',
                'dosen_noHp'  => '08144444444',
            ],
        ];

        DB::table('m_dosen')->insert($data);
    }
}
