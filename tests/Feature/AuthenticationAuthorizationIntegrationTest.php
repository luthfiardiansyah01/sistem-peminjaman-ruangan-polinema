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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 25.1, 27.4**
 * Integration tests for authentication and authorization flow with different user roles.
 */
class AuthenticationAuthorizationIntegrationTest extends TestCase
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
        KelasModel::create(['prodi_id' => 1, 'kelas_nama' => 'Kelas A']);

        $this->createUsersForRoles();
    }

    protected function createUsersForRoles()
    {
        $adminUser = UserModel::create(['username' => 'admin', 'password' => Hash::make('admin123'), 'level_id' => 1]);
        AdminModel::create([
            'user_id' => $adminUser->user_id,
            'admin_nama' => 'Super Admin',
            'admin_nidn' => '0000000000',
            'admin_noHp' => '08111111111',
            'prodi_id' => 1,
        ]);

        $dosenUser = UserModel::create(['username' => 'dosen', 'password' => Hash::make('dosen123'), 'level_id' => 2]);
        DosenModel::create([
            'user_id' => $dosenUser->user_id,
            'dosen_nip_nidn' => '1111111111',
            'dosen_nama' => 'Dr. Dosen',
            'dosen_noHp' => '08222222222',
            'prodi_id' => 1,
        ]);

        $tendikUser = UserModel::create(['username' => 'tendik', 'password' => Hash::make('tendik123'), 'level_id' => 3]);
        TendikModel::create([
            'user_id' => $tendikUser->user_id,
            'tendik_nidn' => '2222222222',
            'tendik_nama' => 'Tenaga Kependidikan',
            'tendik_noHp' => '08333333333',
        ]);

        $mahasiswaUser = UserModel::create(['username' => 'mahasiswa', 'password' => Hash::make('mahasiswa123'), 'level_id' => 4]);
        MahasiswaModel::create([
            'user_id' => $mahasiswaUser->user_id,
            'mahasiswa_nim' => '2024001234',
            'mahasiswa_nama' => 'Mahasiswa Baru',
            'mahasiswa_noHp' => '08444444444',
            'prodi_id' => 1,
            'kelas_id' => 1,
        ]);
    }

    public function test_login_redirects_authenticated_users_to_dashboard()
    {
        $admin = UserModel::where('username', 'admin')->first();
        $this->actingAs($admin);

        $response = $this->get('/login');
        $response->assertRedirect('/dashboard');
    }

    public function test_landing_page_redirects_authenticated_users()
    {
        $dosen = UserModel::where('username', 'dosen')->first();
        $this->actingAs($dosen);

        // "/" is now the public guestDashboard route — it returns 200 for everyone
        // (authenticated or not). Authenticated users who want their personal
        // dashboard must navigate to /dashboard explicitly.
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Root "/" is now the public guestDashboard (stats-only page, no login required).
     * Guests receive 200 — not a redirect — because the route is intentionally outside
     * the 'auth' middleware group (see routes/web.php).
     */
    public function test_landing_page_redirects_guests_to_login()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_pages()
    {
        $admin = UserModel::where('username', 'admin')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.index');

        $response = $this->get('/dosen');
        $response->assertStatus(200);

        $response = $this->get('/mahasiswa');
        $response->assertStatus(200);

        $response = $this->get('/tendik');
        $response->assertStatus(200);
    }

    public function test_dosen_cannot_access_admin_pages()
    {
        $dosen = UserModel::where('username', 'dosen')->first();
        $this->actingAs($dosen);

        // AuthorizeUser middleware calls abort(403, ...) for a role mismatch
        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_user_management_pages()
    {
        $mahasiswa = UserModel::where('username', 'mahasiswa')->first();
        $this->actingAs($mahasiswa);

        $response = $this->get('/dosen');
        $response->assertStatus(403);

        $response = $this->get('/tendik');
        $response->assertStatus(403);
    }

    public function test_logout_clears_authentication_for_all_roles()
    {
        foreach (['admin', 'dosen', 'mahasiswa'] as $username) {
            $user = UserModel::where('username', $username)->first();
            $this->actingAs($user);
            $this->assertAuthenticated();

            $response = $this->get('/logout');
            $response->assertRedirect('/');
            $this->assertGuest();
        }
    }

    public function test_role_based_welcome_message_on_login()
    {
        $cases = [
            ['username' => 'admin', 'password' => 'admin123', 'expectedName' => 'Super Admin'],
            ['username' => 'dosen', 'password' => 'dosen123', 'expectedName' => 'Dr. Dosen'],
            ['username' => 'mahasiswa', 'password' => 'mahasiswa123', 'expectedName' => 'Mahasiswa Baru'],
        ];

        foreach ($cases as $case) {
            $response = $this->postJson('/login', ['username' => $case['username'], 'password' => $case['password']]);
            $response->assertStatus(200);
            $response->assertJson(['status' => true]);
            $this->assertStringContainsString('Selamat datang, ' . $case['expectedName'], $response->json('message'));
            $this->assertAuthenticated();

            $this->get('/logout');
        }
    }

    public function test_invalid_login_attempts_do_not_authenticate()
    {
        $attempts = [
            ['username' => 'admin', 'password' => 'wrongpassword'],
            ['username' => 'nonexistent', 'password' => 'password'],
            ['username' => '', 'password' => ''],
        ];

        foreach ($attempts as $credentials) {
            $response = $this->postJson('/login', $credentials);
            $response->assertStatus(200);
            $response->assertJson([
                'status' => false,
                'message' => 'Login gagal, username atau password salah.',
            ]);
            $this->assertGuest();
        }
    }

    public function test_session_persistence_across_requests()
    {
        $response = $this->postJson('/login', ['username' => 'admin', 'password' => 'admin123']);
        $response->assertJson(['status' => true]);
        $this->assertAuthenticated();

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $this->assertAuthenticated();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function test_ajax_endpoints_require_authentication()
    {
        // For AJAX/JSON requests, App\Exceptions\Handler::renderJsonError() converts the
        // AuthenticationException thrown by the `auth` middleware into a 403 JSON payload
        // carrying a `redirect_url` detail (client-side JS handles the redirect), rather
        // than an HTTP redirect response.
        $response = $this->getJson('/admin/create_ajax');
        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'error_code' => 'UNAUTHORIZED']);
        $response->assertJsonPath('details.redirect_url', route('login'));

        $response = $this->postJson('/admin/ajax', []);
        $response->assertStatus(403);

        $response = $this->getJson('/dosen/import');
        $response->assertStatus(403);
    }

    public function test_ajax_endpoints_work_with_authentication()
    {
        $admin = UserModel::where('username', 'admin')->first();
        $this->actingAs($admin);

        $response = $this->getJson('/admin/create_ajax');
        $response->assertStatus(200);

        $response = $this->getJson('/dosen/import');
        $response->assertStatus(200);

        $response = $this->getJson('/mahasiswa/create_ajax');
        $response->assertStatus(200);
    }

    public function test_form_based_login_redirects_to_dashboard()
    {
        $response = $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_model_role_methods_work_correctly()
    {
        $admin = UserModel::where('username', 'admin')->first();
        $dosen = UserModel::where('username', 'dosen')->first();
        $mahasiswa = UserModel::where('username', 'mahasiswa')->first();

        $this->assertTrue($admin->hasRole('ADM'));
        $this->assertFalse($admin->hasRole('DSN'));

        $this->assertTrue($dosen->hasRole('DSN'));
        $this->assertFalse($dosen->hasRole('ADM'));

        $this->assertTrue($mahasiswa->hasRole('MHS'));
        $this->assertFalse($mahasiswa->hasRole('DSN'));

        $this->assertEquals('Super Admin', $admin->getDisplayName());
        $this->assertEquals('Dr. Dosen', $dosen->getDisplayName());
        $this->assertEquals('Mahasiswa Baru', $mahasiswa->getDisplayName());

        $this->assertEquals('Administrator', $admin->getRoleName());
        $this->assertEquals('Dosen', $dosen->getRoleName());
        $this->assertEquals('Mahasiswa', $mahasiswa->getRoleName());
    }

    public function test_cross_role_authorization_scenarios()
    {
        $admin = UserModel::where('username', 'admin')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response = $this->get('/dosen');
        $response->assertStatus(200);
        $response = $this->get('/mahasiswa');
        $response->assertStatus(200);
        $response = $this->get('/tendik');
        $response->assertStatus(200);

        $dosen = UserModel::where('username', 'dosen')->first();
        $this->actingAs($dosen);
        $response = $this->get('/admin');
        $response->assertStatus(403);

        $mahasiswa = UserModel::where('username', 'mahasiswa')->first();
        $this->actingAs($mahasiswa);
        $response = $this->get('/admin');
        $response->assertStatus(403);
        $response = $this->get('/dosen');
        $response->assertStatus(403);
    }
}
