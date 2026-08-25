<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserModel;
use App\Models\LevelModel;
use App\Models\AdminModel;
use App\Models\ProdiModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 7.1, 10.1, 10.4**
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    protected function createTestData()
    {
        // Create admin level
        $level = LevelModel::create([
            'level_id' => 1,
            'level_kode' => 'ADM',
            'level_nama' => 'Administrator'
        ]);

        // Create test user
        $user = UserModel::create([
            'user_id' => 1,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'level_id' => 1
        ]);

        ProdiModel::create([
            'prodi_id' => 1,
            'prodi_kode' => 'TI',
            'prodi_nama' => 'Teknik Informatika',
        ]);

        // Create admin profile
        AdminModel::create([
            'admin_id' => 1,
            'user_id' => 1,
            'admin_nama' => 'Test Administrator',
            'admin_nidn' => '123456789',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1
        ]);
    }

    public function test_login_page_displays_correctly()
    {
        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_user_redirects_from_login_page()
    {
        // Arrange
        $user = UserModel::first();
        $this->actingAs($user);

        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertRedirect('/dashboard');
    }

    public function test_login_with_valid_credentials_returns_success_json()
    {
        // Arrange
        $credentials = [
            'username' => 'admin',
            'password' => 'password'
        ];

        // Act
        $response = $this->postJson('/login', $credentials);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'redirect' => url('/dashboard')
        ]);
        $response->assertJsonStructure([
            'status',
            'message',
            'redirect'
        ]);
        
        // Check that user is authenticated
        $this->assertAuthenticated();
    }

    public function test_login_with_invalid_credentials_returns_failure_json()
    {
        // Arrange
        $credentials = [
            'username' => 'admin',
            'password' => 'wrongpassword'
        ];

        // Act
        $response = $this->postJson('/login', $credentials);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'status' => false,
            'message' => 'Login gagal, username atau password salah.'
        ]);
        
        // Check that user is not authenticated
        $this->assertGuest();
    }

    public function test_login_with_nonexistent_user_returns_failure()
    {
        // Arrange
        $credentials = [
            'username' => 'nonexistent',
            'password' => 'password'
        ];

        // Act
        $response = $this->postJson('/login', $credentials);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'status' => false,
            'message' => 'Login gagal, username atau password salah.'
        ]);
        
        // Check that user is not authenticated
        $this->assertGuest();
    }

    public function test_logout_clears_authentication_and_redirects()
    {
        // Arrange
        $user = UserModel::first();
        $this->actingAs($user);
        
        // Verify user is authenticated
        $this->assertAuthenticated();

        // Act
        $response = $this->get('/logout');

        // Assert
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_post_login_without_ajax_redirects()
    {
        // Arrange
        $credentials = [
            'username' => 'admin',
            'password' => 'password'
        ];

        // Act - Make regular POST request (not AJAX)
        $response = $this->post('/login', $credentials);

        // Assert
        $response->assertRedirect('/dashboard');
    }

    public function test_login_success_message_includes_user_name()
    {
        // Arrange
        $credentials = [
            'username' => 'admin',
            'password' => 'password'
        ];

        // Act
        $response = $this->postJson('/login', $credentials);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'redirect'
        ]);

        $data = $response->json();
        $this->assertTrue($data['status']);
        $this->assertStringContainsString('Selamat datang, Test Administrator', $data['message']);
    }

    public function test_login_requires_username_and_password()
    {
        // Test missing username
        $response = $this->postJson('/login', ['password' => 'password']);
        $response->assertJson([
            'status' => false,
            'message' => 'Login gagal, username atau password salah.'
        ]);

        // Test missing password
        $response = $this->postJson('/login', ['username' => 'admin']);
        $response->assertJson([
            'status' => false,
            'message' => 'Login gagal, username atau password salah.'
        ]);

        // Test empty credentials
        $response = $this->postJson('/login', []);
        $response->assertJson([
            'status' => false,
            'message' => 'Login gagal, username atau password salah.'
        ]);
    }
}
