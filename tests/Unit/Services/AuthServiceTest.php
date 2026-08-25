<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use App\Models\LevelModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Mockery;

/**
 * **Validates: Requirements 7.1, 10.1, 10.4**
 */
class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_with_valid_credentials_returns_success()
    {
        // Arrange
        $credentials = ['username' => 'admin', 'password' => 'password'];
        
        // Mock Auth::attempt to return true
        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn(true);

        // Mock Auth::user to return a user
        $mockUser = new UserModel(['level_id' => 1, 'username' => 'admin']);
        $mockUser->setRelation('admin', new AdminModel(['admin_nama' => 'Test Admin']));
        
        Auth::shouldReceive('user')->andReturn($mockUser);

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertTrue($result['status']);
        $this->assertStringContainsString('Selamat datang, Test Admin', $result['message']);
        $this->assertEquals(url('/dashboard'), $result['redirect']);
    }

    public function test_login_with_invalid_credentials_returns_failure()
    {
        // Arrange
        $credentials = ['username' => 'invalid', 'password' => 'wrong'];
        
        // Mock Auth::attempt to return false
        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'username' => 'invalid',
                'password' => 'wrong',
            ])
            ->andReturn(false);

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertEquals('Login gagal, username atau password salah.', $result['message']);
        $this->assertArrayNotHasKey('redirect', $result);
    }

    public function test_login_with_missing_credentials_returns_failure_without_attempting_auth()
    {
        // Arrange
        $credentials = ['username' => '', 'password' => ''];

        Auth::shouldReceive('attempt')->never();

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertEquals('Login gagal, username atau password salah.', $result['message']);
    }

    public function test_get_user_display_name_for_admin()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 1, 'username' => 'admin']);
        $mockUser->setRelation('admin', new AdminModel(['admin_nama' => 'Admin Name']));

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Admin Name', $displayName);
    }

    public function test_get_user_display_name_for_dosen()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 2, 'username' => 'dosen']);
        $mockUser->setRelation('dosen', new DosenModel(['dosen_nama' => 'Dosen Name']));

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Dosen Name', $displayName);
    }

    public function test_get_user_display_name_for_tendik()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 3, 'username' => 'tendik']);
        $mockUser->setRelation('tendik', new TendikModel(['tendik_nama' => 'Tendik Name']));

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Tendik Name', $displayName);
    }

    public function test_get_user_display_name_for_mahasiswa()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 4, 'username' => 'mahasiswa']);
        $mockUser->setRelation('mahasiswa', new MahasiswaModel(['mahasiswa_nama' => 'Mahasiswa Name']));

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Mahasiswa Name', $displayName);
    }

    public function test_get_user_display_name_fallback_to_username()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 99, 'username' => 'testuser']);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('testuser', $displayName);
    }

    public function test_is_authenticated_returns_auth_status()
    {
        // Test when user is authenticated
        Auth::shouldReceive('check')->once()->andReturn(true);
        $this->assertTrue($this->authService->isAuthenticated());

        // Test when user is not authenticated
        Auth::shouldReceive('check')->once()->andReturn(false);
        $this->assertFalse($this->authService->isAuthenticated());
    }

    public function test_logout_clears_session_and_regenerates_token()
    {
        // Arrange
        $request = Mockery::mock(Request::class);
        $session = Mockery::mock(\Illuminate\Session\Store::class);
        
        $request->shouldReceive('session')->andReturn($session);
        $session->shouldReceive('invalidate')->once();
        $session->shouldReceive('regenerateToken')->once();

        Auth::shouldReceive('logout')->once();

        // Act
        $this->authService->logout($request);

        // Assert - No exceptions thrown means success
        $this->assertTrue(true);
    }

    public function test_login_handles_exception_gracefully()
    {
        // Arrange
        $credentials = ['username' => 'admin', 'password' => 'password'];
        
        // Mock Auth::attempt to throw exception
        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andThrow(new \Exception('Database connection failed'));

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertEquals('Terjadi kesalahan sistem. Silakan coba lagi.', $result['message']);
    }

    public function test_get_user_display_name_handles_null_relationship()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 1, 'username' => 'admin']);
        $mockUser->setRelation('admin', null);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Admin', $displayName); // Should return default fallback
    }

    public function test_logout_handles_session_exception_gracefully()
    {
        // Arrange
        $request = Mockery::mock(Request::class);
        $session = Mockery::mock(\Illuminate\Session\Store::class);
        
        $request->shouldReceive('session')->andReturn($session);
        $session->shouldReceive('invalidate')->once();
        $session->shouldReceive('regenerateToken')->once();

        Auth::shouldReceive('logout')->once()
            ->andThrow(new \Exception('Logout error'));

        // Act - Should handle exception gracefully
        $this->authService->logout($request);

        // Assert - No exception thrown means success
        $this->assertTrue(true);
    }

    public function test_login_with_only_username_provided_returns_failure()
    {
        // Arrange
        $credentials = ['username' => 'admin', 'password' => ''];

        Auth::shouldReceive('attempt')->never();

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertEquals('Login gagal, username atau password salah.', $result['message']);
    }

    public function test_login_with_only_password_provided_returns_failure()
    {
        // Arrange
        $credentials = ['username' => '', 'password' => 'password'];

        Auth::shouldReceive('attempt')->never();

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertFalse($result['status']);
        $this->assertEquals('Login gagal, username atau password salah.', $result['message']);
    }

    public function test_get_user_display_name_with_no_person_relationships()
    {
        // Arrange
        $mockUser = new UserModel(['level_id' => 1, 'username' => 'admin']);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('getUserDisplayName');
        $method->setAccessible(true);

        // Act
        $displayName = $method->invokeArgs($this->authService, [$mockUser]);

        // Assert
        $this->assertEquals('Admin', $displayName); // Should return default based on level
    }
}