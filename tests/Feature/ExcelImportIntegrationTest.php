<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\KelasModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\MahasiswaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * **Validates: Requirements 25.1, 27.4**
 * Integration tests for Excel import workflow with validation and rollback scenarios.
 *
 * Route contract: POST /{prefix}/import_ajax with file field 'file_dosen'/'file_mahasiswa'
 * (see DosenController::import_ajax / MahasiswaController::import_ajax). Response envelope
 * comes from ImportService::processSheetRows(): {success, message, count, errors[]}.
 */
class ExcelImportIntegrationTest extends TestCase
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
        LevelModel::create(['level_kode' => 'MHS', 'level_nama' => 'Mahasiswa']);

        ProdiModel::create(['prodi_kode' => 'TI', 'prodi_nama' => 'Teknik Informatika']);
        ProdiModel::create(['prodi_kode' => 'SI', 'prodi_nama' => 'Sistem Informasi']);

        KelasModel::create(['prodi_id' => 1, 'kelas_nama' => 'Kelas A']);
        KelasModel::create(['prodi_id' => 1, 'kelas_nama' => 'Kelas B']);
        KelasModel::create(['prodi_id' => 2, 'kelas_nama' => 'Kelas C']);

        $adminUser = UserModel::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'level_id' => 1,
        ]);
        AdminModel::create([
            'user_id' => $adminUser->user_id,
            'admin_nama' => 'Super Admin',
            'admin_nidn' => '0000000000',
            'admin_noHp' => '08111111111',
            'prodi_id' => 1,
        ]);
    }

    /**
     * Build a real .xlsx file so PhpSpreadsheet's IOFactory (used by ImportService)
     * can read it — a CSV masquerading as .xlsx is not reliably auto-detected.
     */
    protected function createExcelFile(array $header, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'excel_test_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'test_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_dosen_excel_import_successful_workflow()
    {
        $this->actingAs(UserModel::first());

        $response = $this->getJson('/dosen/import');
        $response->assertStatus(200);

        $excelRows = [
            ['1234567890', 'Dosen Satu', 'Teknik Informatika', '08123456789'],
            ['0987654321', 'Dosen Dua', 'Sistem Informasi', '08198765432'],
            ['1122334455', 'Dosen Tiga', 'Teknik Informatika', '08112233445'],
        ];

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '3 data dosen berhasil diimpor',
        ]);

        foreach ($excelRows as $row) {
            $this->assertDatabaseHas('m_user', ['username' => $row[0], 'level_id' => 2]);
            $user = UserModel::where('username', $row[0])->first();
            $this->assertNotNull($user);
            $this->assertDatabaseHas('m_dosen', [
                'user_id' => $user->user_id,
                'dosen_nip_nidn' => $row[0],
                'dosen_nama' => $row[1],
            ]);
        }

        $this->assertEquals(3, DosenModel::count());
        $this->assertEquals(4, UserModel::count()); // 1 admin + 3 dosen
    }

    public function test_mahasiswa_excel_import_successful_workflow()
    {
        $this->actingAs(UserModel::first());

        $excelRows = [
            ['2024000001', 'Mahasiswa Satu', 'Teknik Informatika', 'Kelas A', '08111111111'],
            ['2024000002', 'Mahasiswa Dua', 'Teknik Informatika', 'Kelas B', '08122222222'],
            ['2024000003', 'Mahasiswa Tiga', 'Sistem Informasi', 'Kelas C', '08133333333'],
        ];

        $file = $this->createExcelFile(['NIM', 'Nama', 'Program Studi', 'Kelas', 'No HP'], $excelRows);

        $response = $this->postJson('/mahasiswa/import_ajax', ['file_mahasiswa' => $file]);

        // MahasiswaController::importResponse() uses a custom envelope ('status', not
        // 'success') unlike Dosen/TendikController which go through the shared
        // ErrorResponse/jsonResponse trait — this is existing, pre-session behavior.
        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => '3 data mahasiswa berhasil diimpor',
        ]);

        foreach ($excelRows as $row) {
            $this->assertDatabaseHas('m_user', ['username' => $row[0], 'level_id' => 3]);
            $user = UserModel::where('username', $row[0])->first();
            $this->assertNotNull($user);
            $this->assertDatabaseHas('m_mahasiswa', [
                'user_id' => $user->user_id,
                'mahasiswa_nim' => $row[0],
                'mahasiswa_nama' => $row[1],
            ]);
        }

        $this->assertEquals(3, MahasiswaModel::count());
    }

    public function test_excel_import_with_duplicate_nidn_rolls_back_properly()
    {
        $this->actingAs(UserModel::first());

        $existingDosen = UserModel::create([
            'username' => '9999999999',
            'password' => Hash::make('password'),
            'level_id' => 2,
        ]);
        DosenModel::create([
            'user_id' => $existingDosen->user_id,
            'dosen_nip_nidn' => '9999999999',
            'dosen_nama' => 'Existing Dosen',
            'prodi_id' => 1,
            'dosen_noHp' => '08199999999',
        ]);

        $excelRows = [
            ['1111111111', 'Dosen Baru 1', 'Teknik Informatika', '08111111111'],
            ['9999999999', 'Dosen Duplikat', 'Teknik Informatika', '08199999999'], // Duplicate
            ['2222222222', 'Dosen Baru 2', 'Teknik Informatika', '08222222222'],
        ];

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        // Result includes a populated 'errors' array, so ErrorResponse::fromServiceResult()
        // routes it through validationError() → HTTP 422 (see ErrorResponse.php)
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak ada data yang berhasil diimpor',
        ]);
        $response->assertJsonStructure(['details' => ['validation_errors']]);

        // Transaction rolled back: only the pre-existing dosen remains
        $this->assertEquals(1, DosenModel::count());
        $this->assertDatabaseHas('m_dosen', ['dosen_nip_nidn' => '9999999999', 'dosen_nama' => 'Existing Dosen']);
        $this->assertDatabaseMissing('m_dosen', ['dosen_nip_nidn' => '1111111111']);
        $this->assertDatabaseMissing('m_dosen', ['dosen_nip_nidn' => '2222222222']);
    }

    public function test_excel_import_with_invalid_prodi_rolls_back()
    {
        $this->actingAs(UserModel::first());

        $excelRows = [
            ['3333333333', 'Dosen Valid', 'Teknik Informatika', '08333333333'],
            ['4444444444', 'Dosen Invalid Prodi', 'Prodi Tidak Ada', '08444444444'], // Invalid prodi name
            ['5555555555', 'Dosen Valid 2', 'Sistem Informasi', '08555555555'],
        ];

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $response->assertJsonStructure(['details' => ['validation_errors']]);

        $this->assertEquals(0, DosenModel::count());
        $this->assertDatabaseMissing('m_user', ['username' => '3333333333']);
        $this->assertDatabaseMissing('m_user', ['username' => '5555555555']);
    }

    public function test_excel_import_with_mixed_valid_invalid_rows()
    {
        $this->actingAs(UserModel::first());

        $excelRows = [
            ['6666666666', 'Dosen Valid Only', 'Teknik Informatika', '08666666666'],
            ['', 'Dosen No NIDN', 'Teknik Informatika', '08777777777'], // Invalid: missing NIDN
            ['7777777777', '', 'Teknik Informatika', '08888888888'], // Invalid: missing name
        ];

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $response->assertJsonStructure(['details' => ['validation_errors']]);

        // Atomic transaction: even the one valid row is rolled back
        $this->assertEquals(0, DosenModel::count());
        $this->assertDatabaseMissing('m_user', ['username' => '6666666666']);
    }

    public function test_excel_import_empty_file_handling()
    {
        $this->actingAs(UserModel::first());

        // Header row only, no data rows
        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], []);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        // No 'errors' key in this result branch → ErrorResponse::fromServiceResult()
        // falls through to businessLogicError() → HTTP 400
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'File kosong atau format salah',
        ]);

        $this->assertEquals(0, DosenModel::count());
    }

    public function test_excel_import_with_missing_optional_phone()
    {
        $this->actingAs(UserModel::first());

        $excelRows = [
            ['8888888888', 'Dosen No Phone', 'Teknik Informatika', ''], // Empty phone (optional)
            ['9999999999', 'Dosen With Phone', 'Sistem Informasi', '08999999999'],
        ];

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(2, DosenModel::count());

        $user1 = UserModel::where('username', '8888888888')->first();
        $dosen1 = DosenModel::where('user_id', $user1->user_id)->first();
        $this->assertEquals('', $dosen1->dosen_noHp);

        $user2 = UserModel::where('username', '9999999999')->first();
        $dosen2 = DosenModel::where('user_id', $user2->user_id)->first();
        $this->assertEquals('08999999999', $dosen2->dosen_noHp);
    }

    public function test_large_excel_file_chunk_processing()
    {
        $this->actingAs(UserModel::first());

        $excelRows = [];
        for ($i = 1; $i <= 10; $i++) {
            $nidn = str_pad((string) $i, 10, '0', STR_PAD_LEFT);
            $excelRows[] = [
                $nidn,
                "Dosen {$i}",
                ($i % 2 == 0) ? 'Teknik Informatika' : 'Sistem Informasi',
                "08{$nidn}",
            ];
        }

        $file = $this->createExcelFile(['NIDN', 'Nama', 'Program Studi', 'No HP'], $excelRows);

        $response = $this->postJson('/dosen/import_ajax', ['file_dosen' => $file]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(10, DosenModel::count());
        for ($i = 1; $i <= 10; $i++) {
            $nidn = str_pad((string) $i, 10, '0', STR_PAD_LEFT);
            $this->assertDatabaseHas('m_user', ['username' => $nidn]);
        }
    }
}
