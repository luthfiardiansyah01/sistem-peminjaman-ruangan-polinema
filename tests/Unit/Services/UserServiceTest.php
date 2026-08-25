<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Services\LevelService;
use App\Repositories\EloquentUserRepository;
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\KelasModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Mockery;
use Illuminate\Database\Eloquent\Collection;

/**
 * **Validates: Requirements 7.1, 14.2, 14.3**
 */
class UserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_admin_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        // level_id is not in LevelModel::$fillable (PK), so it must be set explicitly
        $levelModel = new LevelModel(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        $levelModel->level_id = 1;
        
        $levelService->shouldReceive('getLevelByKode')->with('ADM')->andReturn($levelModel);
        
        $adminData = [
            'admin_nidn' => '0123456789',
            'admin_nama' => 'Admin Test',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        $adminModel = new AdminModel($adminData);
        $adminModel->user_id = 1;
        $adminModel->admin_id = 1;
        // UserService::createAdmin() dispatches UserCreated($admin->user) — preload the
        // relation so accessing ->user doesn't trigger a real (unmocked) DB lazy-load.
        $adminModel->setRelation('user', new UserModel(['username' => 'admin123', 'level_id' => 1]));

        $repository->shouldReceive('usernameExists')->with('admin123')->andReturn(false);
        // UserService::createAdmin() re-hashes the plaintext password internally (bcrypt salts
        // randomly per call), so a pre-computed Hash::make() value can never exact-match via
        // Mockery::with() — match structurally instead and verify the hash via Hash::check().
        $repository->shouldReceive('createAdmin')
            ->with(Mockery::on(function ($arg) {
                return $arg['username'] === 'admin123'
                    && $arg['level_id'] === 1
                    && Hash::check('password123', $arg['password']);
            }), $adminData)
            ->andReturn($adminModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'admin123',
            'password' => 'password123',
            'admin_nidn' => '0123456789',
            'admin_nama' => 'Admin Test',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createAdmin($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Data admin berhasil ditambahkan', $result['message']);
        $this->assertArrayHasKey('admin_id', $result);
        $this->assertArrayHasKey('user_id', $result);
    }

    public function test_create_admin_returns_validation_error_when_required_fields_are_missing()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        // level_id is not in LevelModel::$fillable (PK), so it must be set explicitly
        $levelModel = new LevelModel(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        $levelModel->level_id = 1;
        $levelService->shouldReceive('getLevelByKode')->with('ADM')->andReturn($levelModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'admin',
            'password' => 'secret',
        ];

        // Act
        $result = $service->createAdmin($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('wajib diisi', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_admin_returns_error_when_username_already_exists()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        // level_id is not in LevelModel::$fillable (PK), so it must be set explicitly
        $levelModel = new LevelModel(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        $levelModel->level_id = 1;
        $levelService->shouldReceive('getLevelByKode')->with('ADM')->andReturn($levelModel);

        $repository->shouldReceive('usernameExists')->with('existing-admin')->andReturn(true);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'existing-admin',
            'password' => 'secret',
            'admin_nidn' => '12345',
            'admin_nama' => 'Admin Baru',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createAdmin($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Username sudah terdaftar', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_admin_returns_error_when_level_not_found()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelService->shouldReceive('getLevelByKode')->with('ADM')->andReturn(null);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'admin',
            'password' => 'secret',
            'admin_nidn' => '12345',
            'admin_nama' => 'Admin Baru',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createAdmin($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Level Admin tidak ditemukan', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_dosen_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelModel = new LevelModel(['level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelModel->level_id = 2;
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);
        
        $dosenData = [
            'dosen_nidn' => '0123456789',
            'dosen_nama' => 'Dosen Test',
            'dosen_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        $dosenModel = new DosenModel($dosenData);
        $dosenModel->user_id = 1;
        $dosenModel->dosen_id = 1;
        $dosenModel->setRelation('user', new UserModel(['username' => 'dosen123', 'level_id' => 2]));

        $repository->shouldReceive('usernameExists')->with('dosen123')->andReturn(false);
        $repository->shouldReceive('createDosen')
            ->with(Mockery::on(function ($arg) {
                return $arg['username'] === 'dosen123'
                    && $arg['level_id'] === 2
                    && Hash::check('password123', $arg['password']);
            }), $dosenData)
            ->andReturn($dosenModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'dosen123',
            'password' => 'password123',
            'dosen_nidn' => '0123456789',
            'dosen_nama' => 'Dosen Test',
            'dosen_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createDosen($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Data dosen berhasil ditambahkan', $result['message']);
        $this->assertArrayHasKey('dosen_id', $result);
        $this->assertArrayHasKey('user_id', $result);
    }

    public function test_create_tendik_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelModel = new LevelModel(['level_kode' => 'TDK', 'level_nama' => 'Tenaga Kependidikan']);
        $levelModel->level_id = 3;
        $levelService->shouldReceive('getLevelByKode')->with('TDK')->andReturn($levelModel);
        
        $tendikData = [
            'tendik_nidn' => '0123456789',
            'tendik_nama' => 'Tendik Test',
            'tendik_noHp' => '08123456789',
        ];

        $tendikModel = new TendikModel($tendikData);
        $tendikModel->user_id = 1;
        $tendikModel->tendik_id = 1;
        $tendikModel->setRelation('user', new UserModel(['username' => 'tendik123', 'level_id' => 3]));

        $repository->shouldReceive('usernameExists')->with('tendik123')->andReturn(false);
        $repository->shouldReceive('createTendik')
            ->with(Mockery::on(function ($arg) {
                return $arg['username'] === 'tendik123'
                    && $arg['level_id'] === 3
                    && Hash::check('password123', $arg['password']);
            }), $tendikData)
            ->andReturn($tendikModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'tendik123',
            'password' => 'password123',
            'tendik_nidn' => '0123456789',
            'tendik_nama' => 'Tendik Test',
            'tendik_noHp' => '08123456789',
        ];

        // Act
        $result = $service->createTendik($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Data tendik berhasil ditambahkan', $result['message']);
        $this->assertArrayHasKey('tendik_id', $result);
        $this->assertArrayHasKey('user_id', $result);
    }

    public function test_create_mahasiswa_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelModel = new LevelModel(['level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);
        $levelModel->level_id = 4;
        $levelService->shouldReceive('getLevelByKode')->with('MHS')->andReturn($levelModel);
        
        $mahasiswaData = [
            'mahasiswa_nim' => '2024001234',
            'mahasiswa_nama' => 'Mahasiswa Test',
            'mahasiswa_noHp' => '08123456789',
            'prodi_id' => 1,
            'kelas_id' => 1,
        ];

        $mahasiswaModel = new MahasiswaModel($mahasiswaData);
        $mahasiswaModel->user_id = 1;
        $mahasiswaModel->mahasiswa_id = 1;
        $mahasiswaModel->setRelation('user', new UserModel(['username' => 'mahasiswa123', 'level_id' => 4]));

        $repository->shouldReceive('usernameExists')->with('mahasiswa123')->andReturn(false);
        $repository->shouldReceive('createMahasiswa')
            ->with(Mockery::on(function ($arg) {
                return $arg['username'] === 'mahasiswa123'
                    && $arg['level_id'] === 4
                    && Hash::check('password123', $arg['password']);
            }), $mahasiswaData)
            ->andReturn($mahasiswaModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'mahasiswa123',
            'password' => 'password123',
            'mahasiswa_nim' => '2024001234',
            'mahasiswa_nama' => 'Mahasiswa Test',
            'mahasiswa_noHp' => '08123456789',
            'prodi_id' => 1,
            'kelas_id' => 1,
        ];

        // Act
        $result = $service->createMahasiswa($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Data mahasiswa berhasil ditambahkan', $result['message']);
        $this->assertArrayHasKey('mahasiswa_id', $result);
        $this->assertArrayHasKey('user_id', $result);
    }

    public function test_update_user_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $user = new UserModel(['user_id' => 1, 'username' => 'existing-user', 'level_id' => 1]);
        
        $repository->shouldReceive('findById')->with(1)->andReturn($user);
        $repository->shouldReceive('usernameExists')->with('updated-username', 1)->andReturn(false);
        $repository->shouldReceive('update')->with(1, ['username' => 'updated-username'])->andReturn($user);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'updated-username',
        ];

        // Act
        $result = $service->updateUser(1, $data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('User berhasil diperbarui', $result['message']);
    }

    public function test_update_user_returns_error_when_user_not_found()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $repository->shouldReceive('findById')->with(999)->andReturn(null);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'updated-username',
        ];

        // Act
        $result = $service->updateUser(999, $data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('User tidak ditemukan', $result['message']);
    }

    public function test_delete_user_returns_success()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $user = new UserModel(['user_id' => 1, 'username' => 'test-user', 'level_id' => 1]);
        
        $repository->shouldReceive('findById')->with(1)->andReturn($user);
        $repository->shouldReceive('delete')->with(1)->andReturn(true);

        $service = new UserService($repository, $levelService);

        // Act
        $result = $service->deleteUser(1);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('User berhasil dihapus', $result['message']);
    }

    public function test_delete_user_returns_error_when_user_not_found()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $repository->shouldReceive('findById')->with(999)->andReturn(null);

        $service = new UserService($repository, $levelService);

        // Act
        $result = $service->deleteUser(999);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('User tidak ditemukan', $result['message']);
    }

    public function test_find_user_by_id_calls_repository()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        // user_id is the primary key and not mass-assignable via UserModel::$fillable,
        // so it must be set explicitly after construction.
        $user = new UserModel(['username' => 'test-user', 'level_id' => 1]);
        $user->user_id = 1;

        $repository->shouldReceive('findById')->with(1)->andReturn($user);

        $service = new UserService($repository, $levelService);

        // Act
        $result = $service->findUserById(1);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(1, $result->user_id);
    }

    public function test_get_all_users_returns_paginated_results()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $users = new Collection([new UserModel(['user_id' => 1])]);
        $paginator = Mockery::mock(\Illuminate\Pagination\LengthAwarePaginator::class);
        $paginator->shouldReceive('items')->andReturn($users->toArray());
        
        $repository->shouldReceive('getAllPaginated')->with(15)->andReturn($paginator);

        $service = new UserService($repository, $levelService);

        // Act
        $result = $service->getAllUsers(15);

        // Assert
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_create_user_returns_error_for_missing_password()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'newuser',
            'level_id' => 1,
        ];

        // Act
        $result = $service->createUser($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('wajib diisi', $result['message']);
    }

    public function test_update_user_with_empty_password_preserves_existing_password()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $user = new UserModel(['user_id' => 1, 'username' => 'existing-user', 'level_id' => 1]);
        $user->password = Hash::make('oldpassword');
        
        $repository->shouldReceive('findById')->with(1)->andReturn($user);
        $repository->shouldReceive('usernameExists')->with('updated-username', 1)->andReturn(false);
        $repository->shouldReceive('update')->with(1, ['username' => 'updated-username'])->andReturn($user);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'updated-username',
            'password' => '',
        ];

        // Act
        $result = $service->updateUser(1, $data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('User berhasil diperbarui', $result['message']);
    }

    public function test_create_admin_handles_database_exception_gracefully()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        // level_id is not in LevelModel::$fillable (PK), so it must be set explicitly
        $levelModel = new LevelModel(['level_kode' => 'ADM', 'level_nama' => 'Administrator']);
        $levelModel->level_id = 1;
        $levelService->shouldReceive('getLevelByKode')->with('ADM')->andReturn($levelModel);

        $repository->shouldReceive('usernameExists')->with('admin123')->andReturn(false);
        $repository->shouldReceive('createAdmin')->with(Mockery::any(), Mockery::any())
            ->andThrow(new \Exception('Database connection failed'));

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'admin123',
            'password' => 'password123',
            'admin_nidn' => '0123456789',
            'admin_nama' => 'Admin Test',
            'admin_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createAdmin($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal menambahkan admin', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_dosen_handles_database_exception_gracefully()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelModel = new LevelModel(['level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelModel->level_id = 2;
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        $repository->shouldReceive('usernameExists')->with('dosen123')->andReturn(false);
        $repository->shouldReceive('createDosen')->with(Mockery::any(), Mockery::any())
            ->andThrow(new \Exception('Database connection failed'));

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'dosen123',
            'password' => 'password123',
            'dosen_nidn' => '0123456789',
            'dosen_nama' => 'Dosen Test',
            'dosen_noHp' => '08123456789',
            'prodi_id' => 1,
        ];

        // Act
        $result = $service->createDosen($data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal menambahkan dosen', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_create_mahasiswa_allows_null_kelas_id()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $levelModel = new LevelModel(['level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);
        $levelModel->level_id = 4;
        $levelService->shouldReceive('getLevelByKode')->with('MHS')->andReturn($levelModel);
        
        $mahasiswaData = [
            'mahasiswa_nim' => '2024001234',
            'mahasiswa_nama' => 'Mahasiswa Test',
            'mahasiswa_noHp' => '08123456789',
            'prodi_id' => 1,
            'kelas_id' => null,
        ];

        $mahasiswaModel = new MahasiswaModel($mahasiswaData);
        $mahasiswaModel->user_id = 1;
        $mahasiswaModel->mahasiswa_id = 1;
        $mahasiswaModel->setRelation('user', new UserModel(['username' => 'mahasiswa123', 'level_id' => 4]));

        $repository->shouldReceive('usernameExists')->with('mahasiswa123')->andReturn(false);
        $repository->shouldReceive('createMahasiswa')
            ->with(Mockery::on(function ($arg) {
                return $arg['username'] === 'mahasiswa123'
                    && $arg['level_id'] === 4
                    && Hash::check('password123', $arg['password']);
            }), $mahasiswaData)
            ->andReturn($mahasiswaModel);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'mahasiswa123',
            'password' => 'password123',
            'mahasiswa_nim' => '2024001234',
            'mahasiswa_nama' => 'Mahasiswa Test',
            'mahasiswa_noHp' => '08123456789',
            'prodi_id' => 1,
            'kelas_id' => null,
        ];

        // Act
        $result = $service->createMahasiswa($data);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('mahasiswa_id', $result);
    }

    public function test_update_user_with_duplicate_username_returns_error()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        
        $user = new UserModel(['user_id' => 1, 'username' => 'old-username', 'level_id' => 1]);
        
        $repository->shouldReceive('findById')->with(1)->andReturn($user);
        $repository->shouldReceive('usernameExists')->with('existing-username', 1)->andReturn(true);

        $service = new UserService($repository, $levelService);

        $data = [
            'username' => 'existing-username',
        ];

        // Act
        $result = $service->updateUser(1, $data);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Username sudah terdaftar', $result['message']);
    }
}