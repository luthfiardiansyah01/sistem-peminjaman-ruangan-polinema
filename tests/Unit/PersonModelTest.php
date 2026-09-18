<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\MahasiswaModel;
use App\Models\TendikModel;
use App\Models\UserModel;
use App\Models\ProdiModel;
use App\Models\KelasModel;
use App\DTO\ProfileDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_model_validation_method_works()
    {
        $admin = new AdminModel([
            'admin_nidn' => '123456789012',
            'admin_nama' => 'Test Admin',
            'admin_noHp' => '081234567890',
        ]);

        $errors = $admin->validate();
        
        $this->assertEmpty($errors, 'Valid admin data should return empty validation errors');
        
        // Test invalid NIDN
        $admin->admin_nidn = '123';
        $errors = $admin->validate();
        $this->assertArrayHasKey('admin_nidn', $errors);
        
        // Test empty name
        $admin->admin_nidn = '123456789012';
        $admin->admin_nama = '';
        $errors = $admin->validate();
        $this->assertArrayHasKey('admin_nama', $errors);
        
        // Test invalid phone
        $admin->admin_nama = 'Test Admin';
        $admin->admin_noHp = 'invalid';
        $errors = $admin->validate();
        $this->assertArrayHasKey('admin_noHp', $errors);
    }

    /** @test */
    public function admin_model_get_profile_works()
    {
        $admin = new AdminModel([
            'admin_id' => 1,
            'user_id' => 1,
            'admin_nidn' => '123456789012',
            'admin_nama' => 'Test Admin',
            'admin_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profile = $admin->getProfile();
        
        $this->assertInstanceOf(ProfileDTO::class, $profile);
        $this->assertEquals('Test Admin', $profile->displayName);
        $this->assertEquals('123456789012', $profile->personIdentifier);
        $this->assertEquals('081234567890', $profile->phoneNumber);
        $this->assertEquals('ADM', $profile->roleCode);
        $this->assertEquals('Administrator', $profile->roleName);
    }

    /** @test */
    public function admin_model_can_manage_prodi_works()
    {
        $admin = new AdminModel(['prodi_id' => 1]);
        
        // Admin without prodi assignment can manage all prodies
        $admin->prodi_id = null;
        $this->assertTrue($admin->canManageProdi(1));
        $this->assertTrue($admin->canManageProdi(2));
        
        // Admin with prodi assignment can only manage assigned prodi
        $admin->prodi_id = 1;
        $this->assertTrue($admin->canManageProdi(1));
        $this->assertFalse($admin->canManageProdi(2));
    }

    /** @test */
    public function dosen_model_validation_method_works()
    {
        $dosen = new DosenModel([
            'dosen_nip_nidn' => '123456789012',
            'dosen_nama' => 'Test Dosen',
            'dosen_noHp' => '081234567890',
            'prodi_id' => 1,
        ]);

        $errors = $dosen->validate();
        
        $this->assertEmpty($errors, 'Valid dosen data should return empty validation errors');
        
        // Test invalid NIDN
        $dosen->dosen_nip_nidn = '123';
        $errors = $dosen->validate();
        $this->assertArrayHasKey('dosen_nip_nidn', $errors);
        
        // Test empty prodi_id
        $dosen->dosen_nip_nidn = '123456789012';
        $dosen->prodi_id = null;
        $errors = $dosen->validate();
        $this->assertArrayHasKey('prodi_id', $errors);
    }

    /** @test */
    public function dosen_model_get_profile_works()
    {
        $dosen = new DosenModel([
            'dosen_id' => 1,
            'user_id' => 1,
            'dosen_nip_nidn' => '123456789012',
            'dosen_nama' => 'Test Dosen',
            'dosen_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profile = $dosen->getProfile();
        
        $this->assertInstanceOf(ProfileDTO::class, $profile);
        $this->assertEquals('Test Dosen', $profile->displayName);
        $this->assertEquals('123456789012', $profile->personIdentifier);
        $this->assertEquals('081234567890', $profile->phoneNumber);
        $this->assertEquals('DSN', $profile->roleCode);
        $this->assertEquals('Dosen', $profile->roleName);
    }

    /** @test */
    public function dosen_model_teaches_in_prodi_works()
    {
        $dosen = new DosenModel(['prodi_id' => 1]);
        
        $this->assertTrue($dosen->teachesInProdi(1));
        $this->assertFalse($dosen->teachesInProdi(2));
    }

    /** @test */
    public function mahasiswa_model_validation_method_works()
    {
        $mahasiswa = new MahasiswaModel([
            'mahasiswa_nim' => '12345678',
            'mahasiswa_nama' => 'Test Mahasiswa',
            'mahasiswa_noHp' => '081234567890',
            'prodi_id' => 1,
            'kelas_id' => 1,
        ]);

        $errors = $mahasiswa->validate();
        
        $this->assertEmpty($errors, 'Valid mahasiswa data should return empty validation errors');
        
        // Test invalid NIM
        $mahasiswa->mahasiswa_nim = '123';
        $errors = $mahasiswa->validate();
        $this->assertArrayHasKey('mahasiswa_nim', $errors);
        
        // Test empty kelas_id
        $mahasiswa->mahasiswa_nim = '12345678';
        $mahasiswa->kelas_id = null;
        $errors = $mahasiswa->validate();
        $this->assertArrayHasKey('kelas_id', $errors);
    }

    /** @test */
    public function mahasiswa_model_get_profile_works()
    {
        $mahasiswa = new MahasiswaModel([
            'mahasiswa_id' => 1,
            'user_id' => 1,
            'mahasiswa_nim' => '12345678',
            'mahasiswa_nama' => 'Test Mahasiswa',
            'mahasiswa_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profile = $mahasiswa->getProfile();
        
        $this->assertInstanceOf(ProfileDTO::class, $profile);
        $this->assertEquals('Test Mahasiswa', $profile->displayName);
        $this->assertEquals('12345678', $profile->personIdentifier);
        $this->assertEquals('081234567890', $profile->phoneNumber);
        $this->assertEquals('MHS', $profile->roleCode);
        $this->assertEquals('Mahasiswa', $profile->roleName);
    }

    /** @test */
    public function mahasiswa_model_enrolled_in_prodi_works()
    {
        $mahasiswa = new MahasiswaModel(['prodi_id' => 1]);
        
        $this->assertTrue($mahasiswa->isEnrolledInProdi(1));
        $this->assertFalse($mahasiswa->isEnrolledInProdi(2));
    }

    /** @test */
    public function mahasiswa_model_belongs_to_class_works()
    {
        $mahasiswa = new MahasiswaModel(['kelas_id' => 1]);
        
        $this->assertTrue($mahasiswa->belongsToClass(1));
        $this->assertFalse($mahasiswa->belongsToClass(2));
    }

    /** @test */
    public function tendik_model_validation_method_works()
    {
        $tendik = new TendikModel([
            'tendik_nidn' => '1234567890123456',
            'tendik_nama' => 'Test Tendik',
            'tendik_noHp' => '081234567890',
        ]);

        $errors = $tendik->validate();
        
        $this->assertEmpty($errors, 'Valid tendik data should return empty validation errors');
        
        // Test invalid NIDN
        $tendik->tendik_nidn = '123';
        $errors = $tendik->validate();
        $this->assertArrayHasKey('tendik_nidn', $errors);
        
        // Test empty name
        $tendik->tendik_nidn = '1234567890123456';
        $tendik->tendik_nama = '';
        $errors = $tendik->validate();
        $this->assertArrayHasKey('tendik_nama', $errors);
    }

    /** @test */
    public function tendik_model_get_profile_works()
    {
        $tendik = new TendikModel([
            'tendik_id' => 1,
            'user_id' => 1,
            'tendik_nidn' => '1234567890123456',
            'tendik_nama' => 'Test Tendik',
            'tendik_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profile = $tendik->getProfile();
        
        $this->assertInstanceOf(ProfileDTO::class, $profile);
        $this->assertEquals('Test Tendik', $profile->displayName);
        $this->assertEquals('1234567890123456', $profile->personIdentifier);
        $this->assertEquals('081234567890', $profile->phoneNumber);
        $this->assertEquals('TDK', $profile->roleCode);
        $this->assertEquals('Tenaga Kependidikan', $profile->roleName);
    }

    /** @test */
    public function all_models_have_get_display_info_method()
    {
        $admin = new AdminModel([
            'admin_id' => 1,
            'admin_nama' => 'Test Admin',
            'admin_nidn' => '123456789012',
            'admin_noHp' => '081234567890',
        ]);
        
        $dosen = new DosenModel([
            'dosen_id' => 1,
            'dosen_nama' => 'Test Dosen',
            'dosen_nip_nidn' => '123456789012',
            'dosen_noHp' => '081234567890',
        ]);
        
        $mahasiswa = new MahasiswaModel([
            'mahasiswa_id' => 1,
            'mahasiswa_nama' => 'Test Mahasiswa',
            'mahasiswa_nim' => '12345678',
            'mahasiswa_noHp' => '081234567890',
        ]);
        
        $tendik = new TendikModel([
            'tendik_id' => 1,
            'tendik_nama' => 'Test Tendik',
            'tendik_nidn' => '1234567890123456',
            'tendik_noHp' => '081234567890',
        ]);
        
        $adminInfo = $admin->getDisplayInfo();
        $dosenInfo = $dosen->getDisplayInfo();
        $mahasiswaInfo = $mahasiswa->getDisplayInfo();
        $tendikInfo = $tendik->getDisplayInfo();
        
        $this->assertIsArray($adminInfo);
        $this->assertIsArray($dosenInfo);
        $this->assertIsArray($mahasiswaInfo);
        $this->assertIsArray($tendikInfo);
        
        $this->assertEquals('Test Admin', $adminInfo['name']);
        $this->assertEquals('Test Dosen', $dosenInfo['name']);
        $this->assertEquals('Test Mahasiswa', $mahasiswaInfo['name']);
        $this->assertEquals('Test Tendik', $tendikInfo['name']);
    }

    /** @test */
    public function profile_dto_creation_consistent_with_model_methods()
    {
        // This test verifies that ProfileDTO can be created from models
        // and that the getProfile() method works on all person models
        
        $admin = new AdminModel([
            'user_id' => 1,
            'admin_nama' => 'Test Admin',
            'admin_nidn' => '123456789012',
            'admin_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $dosen = new DosenModel([
            'user_id' => 2,
            'dosen_nama' => 'Test Dosen',
            'dosen_nip_nidn' => '123456789012',
            'dosen_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $mahasiswa = new MahasiswaModel([
            'user_id' => 3,
            'mahasiswa_nama' => 'Test Mahasiswa',
            'mahasiswa_nim' => '12345678',
            'mahasiswa_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $tendik = new TendikModel([
            'user_id' => 4,
            'tendik_nama' => 'Test Tendik',
            'tendik_nidn' => '1234567890123456',
            'tendik_noHp' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // All models should have getProfile() method that returns ProfileDTO
        $this->assertInstanceOf(ProfileDTO::class, $admin->getProfile());
        $this->assertInstanceOf(ProfileDTO::class, $dosen->getProfile());
        $this->assertInstanceOf(ProfileDTO::class, $mahasiswa->getProfile());
        $this->assertInstanceOf(ProfileDTO::class, $tendik->getProfile());
        
        // Verify role codes are correct
        $this->assertEquals('ADM', $admin->getProfile()->roleCode);
        $this->assertEquals('DSN', $dosen->getProfile()->roleCode);
        $this->assertEquals('MHS', $mahasiswa->getProfile()->roleCode);
        $this->assertEquals('TDK', $tendik->getProfile()->roleCode);
    }
}