<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catatan: ProdiSeeder, KelasSeeder, RuanganSeeder, UserSeeder, AdminSeeder, DosenSeeder,
        // TendikSeeder, MahasiswaSeeder, JadwalSeeder, JadwalRuanganSeeder TIDAK didaftarkan di sini
        // karena memakai primary key hardcoded dan akan bentrok/duplikat pada database yang sudah
        // terisi data (lihat environment saat ini: m_user sudah berisi puluhan baris).
        // Seeder di bawah ini idempotent (aman dijalankan berulang) dan hanya menambah/menempel
        // data baru (Organisasi, jabatan approval ke dosen/mahasiswa yang sudah ada) tanpa
        // mengganggu data existing.
        $this->call([
            LevelSeeder::class,
            OrganisasiSeeder::class,
            JabatanApprovalSeeder::class,
        ]);
    }
}
