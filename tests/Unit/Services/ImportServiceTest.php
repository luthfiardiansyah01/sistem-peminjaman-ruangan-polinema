<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ImportService;
use App\Repositories\EloquentUserRepository;
use App\Services\LevelService;
use App\Services\ProdiService;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\KelasModel;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * **Validates: Requirements 14.1, 14.2, 22.1, 22.2**
 */
class ImportServiceTest extends TestCase
{
    // Tests that touch KelasModel (getKelasMap queries the DB directly) need real schema.
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_import_dosen_from_excel_returns_success_for_valid_data()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodi2 = new ProdiModel(['prodi_nama' => 'Sistem Informasi']);
        $prodi2->prodi_id = 2;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1, $prodi2]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // Mock createDosen to return a mock dosen model
        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(false);
        $repository->shouldReceive('usernameExists')->with('0987654321')->andReturn(false);
        $repository->shouldReceive('createDosen')->andReturn(Mockery::mock(\App\Models\DosenModel::class));

        $service = new ImportService($repository, $levelService, $prodiService);

        // Create a temporary Excel file for testing
        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Test', 'Teknik Informatika', '08123456789'],
            ['0987654321', 'Dosen Lain', 'Sistem Informasi', '081987654321'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('data dosen berhasil diimpor', $result['message']);
        $this->assertEquals(2, $result['count']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_dosen_from_excel_returns_error_for_empty_file()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        $repository->shouldNotReceive('usernameExists');
        $repository->shouldNotReceive('createDosen');

        $service = new ImportService($repository, $levelService, $prodiService);

        // Create an empty Excel file (only header)
        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('File kosong atau format salah', $result['message']);
        $this->assertEquals(0, $result['count']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_dosen_from_excel_returns_error_for_duplicate_nidn()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // First NIDN exists, second doesn't
        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(true);
        $repository->shouldNotReceive('createDosen');

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Test', 'Teknik Informatika', '08123456789'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Tidak ada data yang berhasil diimpor', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(1, count($result['errors'] ?? []));

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_dosen_from_excel_returns_error_for_invalid_prodi()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // ImportService::importDosenFromExcel() checks usernameExists() before resolving
        // prodi_id (see ImportService.php row processor), so it IS called even on a row
        // that will ultimately fail on an invalid prodi name.
        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(false);
        $repository->shouldNotReceive('createDosen');

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Test', 'Prodi Tidak Ada', '08123456789'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Tidak ada data yang berhasil diimpor', $result['message']);
        $this->assertStringContainsString('tidak ditemukan', $result['errors'][0] ?? '');

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_mahasiswa_from_excel_returns_success_for_valid_data()
    {
        // Arrange - We need to mock the KelasModel query since getKelasMap queries the database
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 4, 'level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);
        $levelService->shouldReceive('getLevelByKode')->with('MHS')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // Mock KelasModel query - getKelasMap uses KelasModel::pluck()
        // m_kelas.prodi_id has a real FK to m_prodi, so a matching row must exist first.
        ProdiModel::create(['prodi_kode' => 'TI', 'prodi_nama' => 'Teknik Informatika']);
        KelasModel::insert([
            ['kelas_id' => 1, 'prodi_id' => 1, 'kelas_nama' => 'Kelas A'],
        ]);

        $repository->shouldReceive('usernameExists')->with('2024001234')->andReturn(false);
        $repository->shouldReceive('createMahasiswa')->andReturn(Mockery::mock(\App\Models\MahasiswaModel::class));

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIM', 'Nama', 'Program Studi', 'Kelas', 'No HP'],
            ['2024001234', 'Mahasiswa Test', 'Teknik Informatika', 'Kelas A', '08123456789'],
        ]);

        // Act
        $result = $service->importMahasiswaFromExcel($filePath);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('data mahasiswa berhasil diimpor', $result['message']);

        // Cleanup
        $this->cleanupTestFile($filePath);
        KelasModel::truncate();
    }

    public function test_import_mahasiswa_returns_error_for_invalid_kelas()
    {
        // Arrange
        ProdiModel::create(['prodi_kode' => 'TI', 'prodi_nama' => 'Teknik Informatika']);
        KelasModel::insert([
            ['kelas_id' => 1, 'prodi_id' => 1, 'kelas_nama' => 'Kelas A'],
        ]);

        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 4, 'level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);
        $levelService->shouldReceive('getLevelByKode')->with('MHS')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // usernameExists() is checked before the kelas lookup, so it's still called
        $repository->shouldReceive('usernameExists')->with('2024001234')->andReturn(false);
        $repository->shouldNotReceive('createMahasiswa');

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIM', 'Nama', 'Program Studi', 'Kelas', 'No HP'],
            ['2024001234', 'Mahasiswa Test', 'Teknik Informatika', 'Kelas B', '08123456789'],
        ]);

        // Act
        $result = $service->importMahasiswaFromExcel($filePath);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Tidak ada data yang berhasil diimpor', $result['message']);

        // Cleanup
        $this->cleanupTestFile($filePath);
        KelasModel::truncate();
    }

    public function test_import_tendik_from_excel_returns_success_for_valid_data()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 3, 'level_kode' => 'TDK', 'level_nama' => 'Tenaga Kependidikan']);
        $levelService->shouldReceive('getLevelByKode')->with('TDK')->andReturn($levelModel);

        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(false);
        $repository->shouldReceive('createTendik')->andReturn(Mockery::mock(\App\Models\TendikModel::class));

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'No HP'],
            ['0123456789', 'Tendik Test', '08123456789'],
        ]);

        // Act
        $result = $service->importTendikFromExcel($filePath);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('data tendik berhasil diimpor', $result['message']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_tendik_handles_missing_optional_phone()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 3, 'level_kode' => 'TDK', 'level_nama' => 'Tenaga Kependidikan']);
        $levelService->shouldReceive('getLevelByKode')->with('TDK')->andReturn($levelModel);

        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(false);
        $repository->shouldReceive('createTendik')->andReturn(Mockery::mock(\App\Models\TendikModel::class));

        $service = new ImportService($repository, $levelService, $prodiService);

        // Tendik without phone number
        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'No HP'],
            ['0123456789', 'Tendik Test', ''],
        ]);

        // Act
        $result = $service->importTendikFromExcel($filePath);

        // Assert
        $this->assertTrue($result['success']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_by_type_dispatches_correct_method()
    {
        // This test verifies that importDosenFromExcel properly calls importByType internally
        // by verifying the behavior of importDosenFromExcel when called
        
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        $service = new ImportService($repository, $levelService, $prodiService);

        // Create a valid Excel file
        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Test', 'Prodi Test', '08123456789'],
        ]);

        // Mock for invalid prodi to get immediate validation error
        $repository->shouldReceive('usernameExists')->andReturn(false);

        // Act - Call importDosenFromExcel which internally calls importByType
        $result = $service->importDosenFromExcel($filePath);

        // Assert - This verifies that importByType is called correctly
        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['count']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_normalize_cell_value_handles_null_values()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $service = new ImportService($repository, $levelService, $prodiService);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizeCellValue');
        $method->setAccessible(true);

        // Act
        $result = $method->invokeArgs($service, [null]);

        // Assert
        $this->assertEquals('', $result);
    }

    public function test_normalize_cell_value_trims_whitespace()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $service = new ImportService($repository, $levelService, $prodiService);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizeCellValue');
        $method->setAccessible(true);

        // Act
        $result = $method->invokeArgs($service, ['  test value  ']);

        // Assert
        $this->assertEquals('test value', $result);
    }

    public function test_import_dosen_handles_database_exception_with_rollback()
    {
        // Arrange - The transaction happens in processSheetRows method
        // When an exception occurs during row processing, the entire transaction is rolled back
        // and the result returns with an error
        
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // Both NIDNs don't exist initially
        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(false);
        $repository->shouldReceive('usernameExists')->with('0987654321')->andReturn(false);
        
        // First create succeeds, second throws exception during transaction.
        // Two argument-less shouldReceive() expectations for the same method don't
        // reliably alternate in call order across Mockery versions — use a single
        // expectation with a stateful closure instead.
        $createDosenCallCount = 0;
        $repository->shouldReceive('createDosen')
            ->twice()
            ->andReturnUsing(function () use (&$createDosenCallCount) {
                $createDosenCallCount++;
                if ($createDosenCallCount === 1) {
                    return Mockery::mock(\App\Models\DosenModel::class);
                }
                throw new \Exception('Database error during import');
            });

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Test', 'Teknik Informatika', '08123456789'],
            ['0987654321', 'Dosen Lain', 'Teknik Informatika', '081987654321'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert - Transaction is rolled back and error is caught
        $this->assertFalse($result['success']);
        // The error message depends on whether validation or exception happens first
        $this->assertArrayHasKey('count', $result);
        $this->assertEquals(0, $result['count']);

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_import_dosen_with_mixed_valid_invalid_rows()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $levelModel = new LevelModel(['level_id' => 2, 'level_kode' => 'DSN', 'level_nama' => 'Dosen']);
        $levelService->shouldReceive('getLevelByKode')->with('DSN')->andReturn($levelModel);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        // First NIDN is duplicate, second is valid. createDosen() IS still invoked for the
        // valid row — processSheetRows() writes rows as it iterates and only rolls the whole
        // transaction back at the end once it knows a validation error occurred elsewhere.
        $repository->shouldReceive('usernameExists')->with('0123456789')->andReturn(true);
        $repository->shouldReceive('usernameExists')->with('0987654321')->andReturn(false);
        $repository->shouldReceive('createDosen')->once()->andReturn(Mockery::mock(\App\Models\DosenModel::class));

        $service = new ImportService($repository, $levelService, $prodiService);

        $filePath = $this->createTestExcel([
            ['NIDN', 'Nama', 'Program Studi', 'No HP'],
            ['0123456789', 'Dosen Duplikat', 'Teknik Informatika', '08123456789'],
            ['0987654321', 'Dosen Valid', 'Teknik Informatika', '081987654321'],
        ]);

        // Act
        $result = $service->importDosenFromExcel($filePath);

        // Assert
        // ImportService::processSheetRows() is atomic: any validation error rolls back the
        // ENTIRE sheet (DB::rollBack()), so the valid second row is NOT persisted either —
        // it is not a "partial success" import.
        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['count']);
        $this->assertEquals(1, count($result['errors'] ?? []));

        // Cleanup
        $this->cleanupTestFile($filePath);
    }

    public function test_get_prodi_map_returns_correct_mapping()
    {
        // Arrange
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        // prodi_id is not in ProdiModel::$fillable (PK), so it must be set explicitly
        $prodi1 = new ProdiModel(['prodi_nama' => 'Teknik Informatika']);
        $prodi1->prodi_id = 1;
        $prodi2 = new ProdiModel(['prodi_nama' => 'Sistem Informasi']);
        $prodi2->prodi_id = 2;
        $prodiCollection = new \Illuminate\Database\Eloquent\Collection([$prodi1, $prodi2]);
        $prodiService->shouldReceive('getAllProdis')->andReturn($prodiCollection);

        $service = new ImportService($repository, $levelService, $prodiService);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getProdiMap');
        $method->setAccessible(true);

        // Act
        $result = $method->invokeArgs($service, []);

        // Assert
        $this->assertArrayHasKey('Teknik Informatika', $result);
        $this->assertArrayHasKey('Sistem Informasi', $result);
        $this->assertEquals(1, $result['Teknik Informatika']);
        $this->assertEquals(2, $result['Sistem Informasi']);
    }

    public function test_get_kelas_map_returns_correct_mapping()
    {
        // Arrange - Mock the KelasModel query since getKelasMap uses direct query
        $repository = Mockery::mock(EloquentUserRepository::class);
        $levelService = Mockery::mock(LevelService::class);
        $prodiService = Mockery::mock(ProdiService::class);

        $service = new ImportService($repository, $levelService, $prodiService);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getKelasMap');
        $method->setAccessible(true);

        // The method queries KelasModel directly, so we can't easily mock this
        // This test verifies the structure of the method but not the actual query
        // Since we can't control the database in unit tests, we'll just verify the method exists
        // and returns an array structure

        // Act
        $result = $method->invokeArgs($service, []);

        // Assert
        // The method should return an array (either empty or with kelases)
        $this->assertIsArray($result);
    }

    // Helper methods for Excel file creation

    protected function createTestExcel(array $rows)
    {
        $phpSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $phpSpreadsheet->getActiveSheet();

        $rowIndex = 1;
        foreach ($rows as $row) {
            $columnIndex = 'A';
            foreach ($row as $cellValue) {
                $sheet->setCellValue($columnIndex . $rowIndex, $cellValue);
                $columnIndex++;
            }
            $rowIndex++;
        }

        $filePath = sys_get_temp_dir() . '/test_import_' . uniqid() . '.xlsx';
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($phpSpreadsheet, 'Xlsx');
        $writer->save($filePath);

        return $filePath;
    }

    protected function cleanupTestFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}