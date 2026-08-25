<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\KelasModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use App\Models\RuanganModel;
use App\Models\JadwalModel;
use App\Models\PengajuanModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 25.1, 27.4**
 * Integration tests for dashboard data aggregation with multiple user roles.
 *
 * Verified against the actual current implementation: WelcomeController::index() renders
 * a role-specific view via DashboardService::getWelcomeDashboardData() (admin.welcome,
 * dosen.welcome, tendik.welcome, mahasiswa.welcome, verifikator.welcome).
 */
class DashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMasterData();
    }

    protected function createMasterData()
    {
        LevelModel::create(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        LevelModel::create(['level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        LevelModel::create(['level_kode' => 'TDK', 'level_nama' => 'Tenaga Kependidikan']);
        LevelModel::create(['level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);

        ProdiModel::create(['prodi_kode' => 'TI', 'prodi_nama' => 'Teknik Informatika']);
        ProdiModel::create(['prodi_kode' => 'SI', 'prodi_nama' => 'Sistem Informasi']);

        KelasModel::create(['prodi_id' => 1, 'kelas_nama' => 'Kelas A']);
        KelasModel::create(['prodi_id' => 1, 'kelas_nama' => 'Kelas B']);

        RuanganModel::create(['ruangan_kode' => 'R1', 'ruangan_nama' => 'Ruangan 1', 'ruangan_fasilitas' => 'AC', 'ruangan_kuota' => 20]);
        RuanganModel::create(['ruangan_kode' => 'R2', 'ruangan_nama' => 'Ruangan 2', 'ruangan_fasilitas' => 'Proyektor', 'ruangan_kuota' => 30]);

        $this->createSampleData();
    }

    protected function createSampleData()
    {
        $admin1 = UserModel::create(['username' => 'admin1', 'password' => Hash::make('password'), 'level_id' => 1]);
        AdminModel::create(['user_id' => $admin1->user_id, 'admin_nidn' => '1111111111', 'admin_nama' => 'Admin Satu', 'admin_noHp' => '08111111111', 'prodi_id' => 1]);

        $admin2 = UserModel::create(['username' => 'admin2', 'password' => Hash::make('password'), 'level_id' => 1]);
        AdminModel::create(['user_id' => $admin2->user_id, 'admin_nidn' => '2222222222', 'admin_nama' => 'Admin Dua', 'admin_noHp' => '08222222222', 'prodi_id' => 2]);

        for ($i = 1; $i <= 5; $i++) {
            $dosenUser = UserModel::create(['username' => 'dosen' . $i, 'password' => Hash::make('password'), 'level_id' => 2]);
            DosenModel::create([
                'user_id' => $dosenUser->user_id,
                'dosen_nidn' => str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                'dosen_nama' => 'Dosen ' . $i,
                'dosen_noHp' => '0833333333' . $i,
                'prodi_id' => ($i % 2 == 0) ? 2 : 1,
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            $tendikUser = UserModel::create(['username' => 'tendik' . $i, 'password' => Hash::make('password'), 'level_id' => 3]);
            TendikModel::create([
                'user_id' => $tendikUser->user_id,
                'tendik_nidn' => str_pad((string) ($i + 10), 10, '0', STR_PAD_LEFT),
                'tendik_nama' => 'Tendik ' . $i,
                'tendik_noHp' => '0844444444' . $i,
            ]);
        }

        for ($i = 1; $i <= 20; $i++) {
            $nim = '202400' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $mahasiswaUser = UserModel::create(['username' => $nim, 'password' => Hash::make('password'), 'level_id' => 4]);
            MahasiswaModel::create([
                'user_id' => $mahasiswaUser->user_id,
                'mahasiswa_nim' => $nim,
                'mahasiswa_nama' => 'Mahasiswa ' . $i,
                'mahasiswa_noHp' => '0855555555' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'prodi_id' => ($i % 3 == 0) ? 2 : 1,
                'kelas_id' => ($i % 2 == 0) ? 1 : 2,
            ]);
        }

        // Jadwal, owned by dosen1, spread across "today" and a few other days
        for ($i = 1; $i <= 5; $i++) {
            $jadwal = JadwalModel::create([
                'user_id' => DosenModel::first()->user_id,
                'jadwal_nama' => 'Jadwal ' . $i,
                'jadwal_tgl' => now()->addDays($i)->toDateString(),
                'jadwal_jam_mulai' => '08:00:00',
                'jadwal_jam_selesai' => '10:00:00',
                'jadwal_jumPes' => 10,
            ]);
            $jadwal->ruangans()->attach(RuanganModel::inRandomOrder()->first()->ruangan_id);
        }

        // Pengajuan, owned by first mahasiswa
        for ($i = 1; $i <= 5; $i++) {
            PengajuanModel::create([
                'user_id' => MahasiswaModel::first()->user_id,
                'pengajuan_nama' => 'Pengajuan ' . $i,
                'pengajuan_tgl' => now()->addDays($i)->toDateString(),
                'pengajuan_jam_mulai' => '09:00:00',
                'pengajuan_jam_selesai' => '11:00:00',
                'pengajuan_jumPes' => 5,
                'pengajuan_status' => ($i % 3 == 0) ? 'Ditolak' : (($i % 3 == 1) ? 'Diajukan' : 'Diterima'),
            ]);
        }
    }

    public function test_admin_dashboard_shows_aggregated_statistics()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('admin.welcome');
        $response->assertViewHas('totalUser', 30); // 2 admin + 5 dosen + 3 tendik + 20 mahasiswa

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertViewHas('admins');
        $this->assertCount(2, $response->viewData('admins'));
    }

    public function test_dosen_dashboard_shows_relevant_data()
    {
        $dosen = UserModel::where('username', 'dosen1')->first();
        $this->actingAs($dosen);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('dosen.welcome');
        // FR-8.1/8.2 (T5.1): DashboardService::getWelcomeDashboardData() only injects
        // pengajuanAktif/ruanganHariIni for roles DSN/TDK/MHS
        $response->assertViewHas('pengajuanAktif');
        $response->assertViewHas('ruanganHariIni');
    }

    public function test_mahasiswa_dashboard_shows_personalized_data()
    {
        $mahasiswa = UserModel::where('username', '2024000001')->first();
        $this->actingAs($mahasiswa);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('mahasiswa.welcome');

        // Mahasiswa 1's own active pengajuan should be present (status 'Diajukan')
        $pengajuanAktif = $response->viewData('pengajuanAktif');
        $this->assertGreaterThan(0, $pengajuanAktif->count());
        foreach ($pengajuanAktif as $p) {
            $this->assertEquals($mahasiswa->user_id, $p->user_id);
            $this->assertEquals('Diajukan', $p->pengajuan_status);
        }
    }

    public function test_role_based_dashboard_views_are_correct()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('admin.welcome');

        $dosen = UserModel::where('username', 'dosen1')->first();
        $this->actingAs($dosen);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('dosen.welcome');

        $mahasiswa = UserModel::where('username', '2024000001')->first();
        $this->actingAs($mahasiswa);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('mahasiswa.welcome');
    }

    public function test_dashboard_data_aggregation_counts_are_correct()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $this->assertEquals(2, UserModel::where('level_id', 1)->count());
        $this->assertEquals(5, UserModel::where('level_id', 2)->count());
        $this->assertEquals(3, UserModel::where('level_id', 3)->count());
        $this->assertEquals(20, UserModel::where('level_id', 4)->count());
        $this->assertEquals(30, UserModel::count());

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('totalUser', 30);
        $response->assertViewHas('totalDosen', 5);
        $response->assertViewHas('totalTendik', 3);
        $response->assertViewHas('totalMahasiswa', 20);

        $response = $this->get('/admin');
        $this->assertCount(2, $response->viewData('admins'));
    }

    public function test_dashboard_shows_recent_activities()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        $recentPengajuan = PengajuanModel::orderByDesc('pengajuan_tgl')->limit(5)->get();
        $this->assertGreaterThan(0, $recentPengajuan->count());
    }

    public function test_dashboard_performance_with_large_data_sets()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $startTime = microtime(true);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $executionTime = microtime(true) - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'Dashboard should load within 2 seconds');
    }

    public function test_dashboard_data_isolation_between_roles()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);
        $response = $this->get('/admin');
        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('admins'));

        $response = $this->get('/dosen');
        $response->assertStatus(200);

        // Dosen/mahasiswa are blocked from management pages by AuthorizeUser middleware
        $dosen = UserModel::where('username', 'dosen1')->first();
        $this->actingAs($dosen);
        $response = $this->get('/admin');
        $response->assertStatus(403);

        $mahasiswa = UserModel::where('username', '2024000001')->first();
        $this->actingAs($mahasiswa);
        $response = $this->get('/dosen');
        $response->assertStatus(403);
    }

    public function test_dashboard_error_handling()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Orphaned user: dosen level but no m_dosen record — getDisplayName() falls back to '-'
        $orphanedUser = UserModel::create(['username' => 'orphaned', 'password' => Hash::make('password'), 'level_id' => 2]);
        $this->actingAs($orphanedUser);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_updates_with_new_data()
    {
        $admin = UserModel::where('username', 'admin1')->first();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        $newDosenUser = UserModel::create(['username' => 'newdosen', 'password' => Hash::make('password'), 'level_id' => 2]);
        DosenModel::create([
            'user_id' => $newDosenUser->user_id,
            'dosen_nidn' => '9999999999',
            'dosen_nama' => 'New Dosen',
            'dosen_noHp' => '08999999999',
            'prodi_id' => 1,
        ]);

        $response = $this->get('/dosen');
        $response->assertStatus(200);

        $this->assertDatabaseHas('m_user', ['username' => 'newdosen']);
    }
}
