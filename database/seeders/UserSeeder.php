<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Admin
            [
                'user_id'   => 1,
                'level_id'  => 1,
                'username'  => 'admin1',
                'password'  => Hash::make('0000000000'),
            ],
            [
                'user_id'   => 5,
                'level_id'  => 1,
                'username'  => '198000000000000000',
                'password'  => Hash::make('198000000000000000')
            ],
            // Dosen (regular + VRF)
            [
                'user_id'   => 2,
                'level_id'  => 2,
                'username'  => 'dosen',
                'password'  => Hash::make('1111111111')
            ],
            [
                'user_id'   => 7,
                'level_id'  => 2,
                'username'  => 'dosen2',
                'password'  => Hash::make('123456')
            ],
            // Tendik
            [
                'user_id'   => 3,
                'level_id'  => 3,
                'username'  => 'tendik1',
                'password'  => Hash::make('0021111111')
            ],
            [
                'user_id'   => 8,
                'level_id'  => 3,
                'username'  => '0013333333',
                'password'  => Hash::make('123456')
            ],
            [
                'user_id'   => 9,
                'level_id'  => 3,
                'username'  => '0014444444',
                'password'  => Hash::make('123456')
            ],
            // Mahasiswa (regular + VRF)
            [
                'user_id'   => 4,
                'level_id'  => 4,
                'username'  => 'mahasiswa1',
                'password'  => Hash::make('0011111111')
            ],
            [
                'user_id'   => 6,
                'level_id'  => 4,
                'username'  => '2241760063',
                'password'  => Hash::make('2241760063')
            ],
            // VRF users - using different NIMs
            [
                'user_id'   => 10,
                'level_id'  => 4,
                'username'  => '2241760003',
                'password'  => Hash::make('123456')
            ],
            [
                'user_id'   => 11,
                'level_id'  => 4,
                'username'  => '2241760010',
                'password'  => Hash::make('123456')
            ],
            
        ];

        DB::table('m_user')->insert($data);
    }
}

