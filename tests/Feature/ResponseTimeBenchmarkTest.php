<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\AdminModel;
use App\Models\RuanganModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 29.1, 29.3**
 * Task 11 (Final verification): records current response times for key endpoints.
 *
 * NOTE: No pre-refactor baseline was recorded anywhere in this repository (no benchmark
 * file, no CI performance job, no historical numbers in .kiro/specs). The "no more than
 * 10% increase vs baseline" requirement cannot be literally checked without a baseline
 * that was never captured. This test instead establishes the CURRENT numbers as the
 * baseline going forward, and asserts a generous absolute ceiling so a future regression
 * is caught even without prior data.
 */
class ResponseTimeBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        LevelModel::create(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        ProdiModel::create(['prodi_kode' => 'TI', 'prodi_nama' => 'Teknik Informatika']);

        $admin = UserModel::create(['username' => 'admin', 'password' => Hash::make('admin123'), 'level_id' => 1]);
        AdminModel::create([
            'user_id' => $admin->user_id,
            'admin_nama' => 'Super Admin',
            'admin_nidn' => '0000000000',
            'admin_noHp' => '08111111111',
            'prodi_id' => 1,
        ]);

        for ($i = 1; $i <= 20; $i++) {
            RuanganModel::create([
                'ruangan_kode' => 'R' . $i,
                'ruangan_nama' => 'Ruangan ' . $i,
                'ruangan_fasilitas' => 'AC',
                'ruangan_kuota' => 20,
                'ruangan_kategori' => 'Jurusan',
            ]);
        }
    }

    protected function benchmark(string $label, \Closure $request): void
    {
        $start = microtime(true);
        $response = $request();
        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        fwrite(STDERR, sprintf("\n  [benchmark] %-30s %6.1f ms\n", $label, $elapsedMs));

        // Generous absolute ceiling (no prior baseline exists to compute a relative
        // +10% threshold against — see class docblock).
        $this->assertLessThan(2000, $elapsedMs, "{$label} took {$elapsedMs}ms, exceeding the 2000ms ceiling");
    }

    public function test_login_response_time()
    {
        $this->benchmark('POST /login', fn () => $this->postJson('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]));
    }

    public function test_dashboard_response_time()
    {
        $this->actingAs(UserModel::first());
        $this->benchmark('GET /dashboard', fn () => $this->get('/dashboard'));
    }

    public function test_ruangan_index_response_time()
    {
        $this->actingAs(UserModel::first());
        $this->benchmark('GET /ruangan', fn () => $this->get('/ruangan'));
    }

    public function test_admin_index_response_time()
    {
        $this->actingAs(UserModel::first());
        $this->benchmark('GET /admin', fn () => $this->get('/admin'));
    }
}
