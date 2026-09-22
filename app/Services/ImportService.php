<?php

namespace App\Services;

use App\Models\KelasModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\ImportServiceInterface;
use App\Services\Interfaces\LevelServiceInterface;
use App\Services\Interfaces\ProdiServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService implements ImportServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var LevelServiceInterface
     */
    protected $levelService;

    /**
     * @var ProdiServiceInterface
     */
    protected $prodiService;

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param LevelServiceInterface $levelService
     * @param ProdiServiceInterface $prodiService
     */
    public function __construct(UserRepositoryInterface $userRepository, LevelServiceInterface $levelService, ProdiServiceInterface $prodiService)
    {
        $this->userRepository = $userRepository;
        $this->levelService = $levelService;
        $this->prodiService = $prodiService;
    }

    /**
     * Validate and import an uploaded Excel file by user type.
     *
     * @param mixed $file
     * @param string $type
     * @return array
     */
    public function importUploadedUserFile($file, string $type): array
    {
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        return $this->importByType($file->getRealPath(), $type);
    }

    /**
     * Import dosen from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importDosenFromExcel(string $filePath): array
    {
        $levelDosen = $this->resolveLevel('DSN', 'Dosen');
        if (!$levelDosen) {
            return $this->levelNotFoundResponse('Dosen');
        }

        $prodiMap = $this->getProdiMap();

        return $this->processSheetRows(
            $filePath,
            function (int $rowIndex, array $rowValues) use ($levelDosen, $prodiMap): array {
                $nidn = $this->normalizeCellValue($rowValues[0] ?? null);
                $nama = $this->normalizeCellValue($rowValues[1] ?? null);
                $prodiName = $this->normalizeCellValue($rowValues[2] ?? null);
                $noHp = $this->normalizeCellValue($rowValues[3] ?? null);

                if ($nidn === '' || $nama === '' || $prodiName === '') {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Data tidak lengkap',
                    ];
                }

                if ($this->userRepository->usernameExists($nidn)) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': NIDN ' . $nidn . ' sudah terdaftar',
                    ];
                }

                $prodiId = $prodiMap[$prodiName] ?? null;
                if (!$prodiId) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Program Studi ' . $prodiName . ' tidak ditemukan',
                    ];
                }

                // Kolom identifier tunggal di Excel: 18 digit dianggap NIP, selain itu NIDN
                // (klien revisi ke-2 poin 4 konfirmasi panjang digit final per jenis).
                $identitasField = strlen($nidn) === 18 ? 'dosen_nip' : 'dosen_nidn';

                $this->userRepository->createDosen([
                    'username' => $nidn,
                    'password' => Hash::make($nidn),
                    'level_id' => $levelDosen->level_id,
                ], [
                    $identitasField => $nidn,
                    'dosen_nama' => $nama,
                    'dosen_noHp' => $noHp,
                    'prodi_id' => $prodiId,
                ]);

                return ['valid' => true];
            },
            'File kosong atau format salah',
            'data dosen berhasil diimpor',
            'Dosen'
        );
    }

    /**
     * Import mahasiswa from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importMahasiswaFromExcel(string $filePath): array
    {
        $levelMahasiswa = $this->resolveLevel('MHS', 'Mahasiswa');
        if (!$levelMahasiswa) {
            return $this->levelNotFoundResponse('Mahasiswa');
        }

        $prodiMap = $this->getProdiMap();
        $kelasMap = $this->getKelasMap();

        return $this->processSheetRows(
            $filePath,
            function (int $rowIndex, array $rowValues) use ($levelMahasiswa, $prodiMap, $kelasMap): array {
                $nim = $this->normalizeCellValue($rowValues[0] ?? null);
                $nama = $this->normalizeCellValue($rowValues[1] ?? null);
                $prodiName = $this->normalizeCellValue($rowValues[2] ?? null);
                $kelasName = $this->normalizeCellValue($rowValues[3] ?? null);
                $noHp = $this->normalizeCellValue($rowValues[4] ?? null);

                if ($nim === '' || $nama === '' || $prodiName === '' || $kelasName === '') {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Data tidak lengkap',
                    ];
                }

                if ($this->userRepository->usernameExists($nim)) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': NIM ' . $nim . ' sudah terdaftar',
                    ];
                }

                $prodiId = $prodiMap[$prodiName] ?? null;
                if (!$prodiId) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Program Studi ' . $prodiName . ' tidak ditemukan',
                    ];
                }

                $kelasId = $kelasMap[$kelasName] ?? null;
                if (!$kelasId) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Kelas ' . $kelasName . ' tidak ditemukan',
                    ];
                }

                $this->userRepository->createMahasiswa([
                    'username' => $nim,
                    'password' => Hash::make($nim),
                    'level_id' => $levelMahasiswa->level_id,
                ], [
                    'mahasiswa_nim' => $nim,
                    'mahasiswa_nama' => $nama,
                    'mahasiswa_noHp' => $noHp,
                    'prodi_id' => $prodiId,
                    'kelas_id' => $kelasId,
                ]);

                return ['valid' => true];
            },
            'File kosong atau format salah',
            'data mahasiswa berhasil diimpor',
            'Mahasiswa'
        );
    }

    /**
     * Import tendik from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importTendikFromExcel(string $filePath): array
    {
        $levelTendik = $this->resolveLevel('TDK', 'Tendik');
        if (!$levelTendik) {
            return $this->levelNotFoundResponse('Tendik');
        }

        return $this->processSheetRows(
            $filePath,
            function (int $rowIndex, array $rowValues) use ($levelTendik): array {
                $nidn = $this->normalizeCellValue($rowValues[0] ?? null);
                $nama = $this->normalizeCellValue($rowValues[1] ?? null);
                $noHp = $this->normalizeCellValue($rowValues[2] ?? null);

                if ($nidn === '' || $nama === '') {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': Data tidak lengkap',
                    ];
                }

                if ($this->userRepository->usernameExists($nidn)) {
                    return [
                        'valid' => false,
                        'error' => 'Baris ' . ($rowIndex + 1) . ': NIDN ' . $nidn . ' sudah terdaftar',
                    ];
                }

                $this->userRepository->createTendik([
                    'username' => $nidn,
                    'password' => Hash::make($nidn),
                    'level_id' => $levelTendik->level_id,
                ], [
                    'tendik_nidn' => $nidn,
                    'tendik_nama' => $nama,
                    'tendik_noHp' => $noHp,
                ]);

                return ['valid' => true];
            },
            'File kosong atau format salah',
            'data tendik berhasil diimpor',
            'Tendik'
        );
    }

    /**
     * Dispatch import processing by supported user type.
     */
    protected function importByType(string $filePath, string $type): array
    {
        return match ($type) {
            'dosen' => $this->importDosenFromExcel($filePath),
            'mahasiswa' => $this->importMahasiswaFromExcel($filePath),
            'tendik' => $this->importTendikFromExcel($filePath),
            default => [
                'success' => false,
                'message' => 'Tipe import tidak valid',
                'status' => 400,
            ],
        };
    }

    /**
     * Process the Excel rows in chunks while keeping the insertions inside one transaction.
     *
     * @param string $filePath
     * @param callable $rowProcessor
     * @param string $emptyMessage
     * @param string $successMessage
     * @param string $label
     * @return array
     */
    protected function processSheetRows(string $filePath, callable $rowProcessor, string $emptyMessage, string $successMessage, string $label): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($filePath);
        } catch (\Throwable $e) {
            Log::error('Import ' . $label . ' File Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
                'count' => 0,
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rowIterator = $sheet->getRowIterator();
        $insertedCount = 0;
        $errors = [];
        $dataRowCount = 0;
        $hasValidationError = false;

        DB::beginTransaction();

        try {
            foreach ($rowIterator as $row) {
                $rowIndex = $row->getRowIndex();
                if ($rowIndex === 1) {
                    continue;
                }

                $dataRowCount++;
                $rowValues = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowValues[] = $cell->getValue();
                }

                $result = $rowProcessor($rowIndex - 2, $rowValues);
                if (($result['valid'] ?? false) === true) {
                    $insertedCount++;
                    continue;
                }

                $hasValidationError = true;
                $errors[] = $result['error'] ?? 'Baris ' . ($rowIndex + 1) . ': Data tidak valid';
            }

            if ($dataRowCount === 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => $emptyMessage,
                    'count' => 0,
                ];
            }

            if ($hasValidationError) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Tidak ada data yang berhasil diimpor',
                    'count' => 0,
                    'errors' => $errors,
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $insertedCount . ' ' . $successMessage,
                'count' => $insertedCount,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import ' . $label . ' Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
                'count' => 0,
                'errors' => $errors,
            ];
        }
    }

    /**
     * Normalize a spreadsheet cell value.
     *
     * @param mixed $value
     * @return string
     */
    protected function normalizeCellValue($value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Resolve a level by code.
     *
     * @param string $code
     * @param string $label
     * @return mixed|null
     */
    protected function resolveLevel(string $code, string $label)
    {
        return $this->levelService->getLevelByKode($code);
    }

    /**
     * Create a standard level-not-found response.
     *
     * @param string $label
     * @return array
     */
    protected function levelNotFoundResponse(string $label): array
    {
        return [
            'success' => false,
            'message' => 'Level ' . $label . ' tidak ditemukan',
            'count' => 0,
        ];
    }

    /**
     * Get prodi map for lookup
     *
     * @return array
     */
    protected function getProdiMap(): array
    {
        $prodis = $this->prodiService->getAllProdis();
        $map = [];
        foreach ($prodis as $prodi) {
            $map[$prodi->prodi_nama] = $prodi->prodi_id;
        }
        return $map;
    }

    /**
     * Get kelas map for lookup
     *
     * @return array
     */
    protected function getKelasMap(): array
    {
        return KelasModel::pluck('kelas_id', 'kelas_nama')->toArray();
    }
}
