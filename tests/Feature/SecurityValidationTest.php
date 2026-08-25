<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\AdminModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 30.1, 30.3**
 * Security validation: SQL injection and XSS prevention.
 *
 * Task 11 (Final verification): "Run security validation for SQL injection and XSS
 * prevention". These tests exercise the actual HTTP layer with malicious payloads and
 * assert the application neither executes injected SQL nor reflects unescaped HTML/JS.
 */
class SecurityValidationTest extends TestCase
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
    }

    /**
     * Classic auth-bypass SQLi payload in the login form. Laravel's Auth::attempt() uses
     * Eloquent (parameterized queries), so this must behave exactly like any other wrong
     * password — no bypass, no SQL error leaking through.
     */
    public function test_login_form_rejects_sql_injection_payload()
    {
        $payloads = [
            "' OR '1'='1",
            "admin' --",
            "admin'; DROP TABLE m_user; --",
            "' OR 1=1 --",
        ];

        foreach ($payloads as $payload) {
            $response = $this->postJson('/login', [
                'username' => $payload,
                'password' => $payload,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['status' => false]);
            $this->assertGuest();
        }

        // Table must still exist and be intact
        $this->assertDatabaseHas('m_user', ['username' => 'admin']);
    }

    /**
     * SQLi payload in the DataTables search box (UserManagementService::applySearch()
     * builds a raw WHERE clause via orWhereRaw with a parameterized '?' placeholder).
     */
    public function test_search_query_parameter_rejects_sql_injection_payload()
    {
        $this->actingAs(UserModel::first());

        $response = $this->postJson('/admin/list', [
            'search' => ['value' => "'; DROP TABLE m_user; --"],
        ]);

        // Must not error out (500) and must not drop the table
        $response->assertStatus(200);
        $this->assertDatabaseHas('m_user', ['username' => 'admin']);
    }

    /**
     * XSS payload stored via a normal CRUD field (ruangan_nama) must come back
     * HTML-escaped when rendered in a Blade view (Blade {{ }} auto-escapes).
     */
    public function test_stored_xss_payload_is_escaped_on_render()
    {
        $this->actingAs(UserModel::first());

        $payload = '<script>alert("xss")</script>';

        $response = $this->postJson('/ruangan/ajax', [
            'ruangan_kode' => 'XSS1',
            'ruangan_nama' => $payload,
            'ruangan_fasilitas' => 'AC',
            'ruangan_kuota' => 10,
            'ruangan_kategori' => 'Jurusan',
        ]);
        $response->assertStatus(201);

        // Raw payload is stored as-is (expected — sanitization happens at render time)
        $this->assertDatabaseHas('m_ruangan', ['ruangan_kode' => 'XSS1', 'ruangan_nama' => $payload]);

        // But the rendered index page must NOT contain the raw <script> tag
        $page = $this->get('/ruangan');
        $page->assertStatus(200);
        $page->assertDontSee('<script>alert("xss")</script>', false);
        // Blade's {{ }} escaping turns it into &lt;script&gt;...
        $page->assertSee('&lt;script&gt;', false);
    }

    /**
     * Same check for the Organisasi module (a newer G1 CRUD addition).
     */
    public function test_organisasi_xss_payload_is_escaped_on_render()
    {
        $this->actingAs(UserModel::first());

        $payload = '<img src=x onerror=alert(1)>';

        $response = $this->postJson('/organisasi/ajax', [
            'organisasi_kode' => 'XSS2',
            'organisasi_nama' => $payload,
        ]);
        $response->assertStatus(201);

        $page = $this->get('/organisasi');
        $page->assertStatus(200);
        $page->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
