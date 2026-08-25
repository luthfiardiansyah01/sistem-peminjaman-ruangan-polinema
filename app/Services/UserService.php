<?php

namespace App\Services;

use App\Services\Interfaces\UserServiceInterface;
use App\Services\Interfaces\LevelServiceInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use App\Models\UserModel;
use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Events\UserDeleted;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UserService implements UserServiceInterface
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
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param LevelServiceInterface $levelService
     */
    public function __construct(UserRepositoryInterface $userRepository, LevelServiceInterface $levelService)
    {
        $this->userRepository = $userRepository;
        $this->levelService = $levelService;
    }

    /**
     * Create a new user with transaction support
     *
     * @param array $data
     * @return array
     */
    public function createUser(array $data): array
    {
        $requiredValidation = $this->validateRequiredFields($data, ['username', 'password', 'level_id']);
        if ($requiredValidation) {
            return $requiredValidation;
        }

        $usernameValidation = $this->validateUniqueUsername($data['username'] ?? '');
        if ($usernameValidation) {
            return $usernameValidation;
        }

        $normalizedData = $this->normalizeUserPayload($data);
        $normalizedData['password'] = Hash::make($normalizedData['password']);

        try {
            return DB::transaction(function () use ($normalizedData): array {
                $user = $this->userRepository->create($normalizedData);
                Event::dispatch(new UserCreated($user));

                return [
                    'success' => true,
                    'message' => 'User berhasil dibuat',
                    'user_id' => $user->user_id
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal membuat user: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Update user data
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateUser(int $userId, array $data): array
    {
        // Check if user exists
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        // Check if username is being changed and if new username exists
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userRepository->usernameExists($data['username'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'Username sudah terdaftar',
                    'errors' => ['username' => 'Username sudah terdaftar']
                ];
            }
        }

        // Hash password if being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } elseif (isset($data['password'])) {
            // If password is set but empty, don't update it
            unset($data['password']);
        }

        // Update user
        try {
            $user = $this->userRepository->update($userId, $data);
            
            // Dispatch event
            Event::dispatch(new UserUpdated($user, $data));
            
            return [
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'user_id' => $user->user_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Delete user by ID
     *
     * @param int $userId
     * @return array
     */
    public function deleteUser(int $userId): array
    {
        // Check if user exists
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        // Delete user
        try {
            // Dispatch event before deletion
            Event::dispatch(new UserDeleted($user));
            
            $this->userRepository->delete($userId);
            
            return [
                'success' => true,
                'message' => 'User berhasil dihapus'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Create admin user
     *
     * @param array $data
     * @return array
     */
    public function createAdmin(array $data): array
    {
        $levelAdmin = $this->resolveLevel('ADM', 'Admin');
        if (!$levelAdmin) {
            return $this->levelNotFoundResponse('Admin');
        }

        $validation = $this->validatePersonPayload($data, [
            'username' => 'username',
            'password' => 'password',
            'admin_nidn' => 'admin_nidn',
            'admin_nama' => 'admin_nama',
            'prodi_id' => 'prodi_id',
        ], 'admin');
        if ($validation) {
            return $validation;
        }

        $userData = [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => Hash::make((string) ($data['password'] ?? '')),
            'level_id' => $levelAdmin->level_id,
        ];

        $adminData = [
            'admin_nidn' => trim((string) ($data['admin_nidn'] ?? '')),
            'admin_nama' => trim((string) ($data['admin_nama'] ?? '')),
            'admin_noHp' => isset($data['admin_noHp']) && $data['admin_noHp'] !== '' ? trim((string) $data['admin_noHp']) : null,
            'prodi_id' => (int) ($data['prodi_id'] ?? 0),
        ];

        try {
            return DB::transaction(function () use ($userData, $adminData): array {
                if ($this->userRepository->usernameExists($userData['username'])) {
                    return [
                        'success' => false,
                        'message' => 'Username sudah terdaftar',
                        'errors' => ['username' => 'Username sudah terdaftar']
                    ];
                }

                $admin = $this->userRepository->createAdmin($userData, $adminData);
                Event::dispatch(new UserCreated($admin->user));

                return [
                    'success' => true,
                    'message' => 'Data admin berhasil ditambahkan',
                    'admin_id' => $admin->admin_id,
                    'user_id' => $admin->user_id
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan admin: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Update admin (m_user + m_admin) sekaligus.
     *
     * updateUser() generik hanya menulis ke m_user, jadi field admin
     * (admin_nama, admin_nidn, admin_noHp, prodi_id) tidak boleh lewat situ.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateAdmin(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        $userData = [];
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userRepository->usernameExists($data['username'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'Username sudah terdaftar',
                    'errors' => ['username' => 'Username sudah terdaftar']
                ];
            }
            $userData['username'] = trim((string) $data['username']);
        }
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make((string) $data['password']);
        }

        $adminData = [
            'admin_nama' => trim((string) ($data['admin_nama'] ?? '')),
            'admin_nidn' => trim((string) ($data['admin_nidn'] ?? '')),
            'admin_noHp' => isset($data['admin_noHp']) && $data['admin_noHp'] !== '' ? trim((string) $data['admin_noHp']) : null,
            'prodi_id' => isset($data['prodi_id']) && $data['prodi_id'] !== '' ? (int) $data['prodi_id'] : null,
        ];

        try {
            $admin = $this->userRepository->updateAdmin($userId, $userData, $adminData);
            Event::dispatch(new UserUpdated($admin->user, $data));

            return [
                'success' => true,
                'message' => 'Data admin berhasil diperbarui',
                'admin_id' => $admin->admin_id,
                'user_id' => $admin->user_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui admin: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Create dosen user
     *
     * @param array $data
     * @return array
     */
    public function createDosen(array $data): array
    {
        $levelDosen = $this->resolveLevel('DSN', 'Dosen');
        if (!$levelDosen) {
            return $this->levelNotFoundResponse('Dosen');
        }

        $validation = $this->validatePersonPayload($data, [
            'username' => 'username',
            'password' => 'password',
            'dosen_nidn' => 'dosen_nidn',
            'dosen_nama' => 'dosen_nama',
            'prodi_id' => 'prodi_id',
        ], 'dosen');
        if ($validation) {
            return $validation;
        }

        $userData = [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => Hash::make((string) ($data['password'] ?? '')),
            'level_id' => $levelDosen->level_id,
        ];

        $dosenData = [
            'dosen_nidn' => trim((string) ($data['dosen_nidn'] ?? '')),
            'dosen_nama' => trim((string) ($data['dosen_nama'] ?? '')),
            'dosen_noHp' => isset($data['dosen_noHp']) && $data['dosen_noHp'] !== '' ? trim((string) $data['dosen_noHp']) : null,
            'prodi_id' => (int) ($data['prodi_id'] ?? 0),
        ];

        try {
            return DB::transaction(function () use ($userData, $dosenData): array {
                if ($this->userRepository->usernameExists($userData['username'])) {
                    return [
                        'success' => false,
                        'message' => 'Username sudah terdaftar',
                        'errors' => ['username' => 'Username sudah terdaftar']
                    ];
                }

                $dosen = $this->userRepository->createDosen($userData, $dosenData);
                Event::dispatch(new UserCreated($dosen->user));

                return [
                    'success' => true,
                    'message' => 'Data dosen berhasil ditambahkan',
                    'dosen_id' => $dosen->dosen_id,
                    'user_id' => $dosen->user_id
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan dosen: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Update dosen (m_user + m_dosen) sekaligus.
     *
     * updateUser() generik hanya menulis ke m_user, jadi field dosen
     * (dosen_nama, dosen_nidn, dosen_noHp, prodi_id) tidak boleh lewat situ.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateDosen(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        $userData = [];
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userRepository->usernameExists($data['username'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'Username sudah terdaftar',
                    'errors' => ['username' => 'Username sudah terdaftar']
                ];
            }
            $userData['username'] = trim((string) $data['username']);
        }
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make((string) $data['password']);
        }

        $dosenData = [
            'dosen_nama' => trim((string) ($data['dosen_nama'] ?? '')),
            'dosen_nidn' => trim((string) ($data['dosen_nidn'] ?? '')),
            'dosen_noHp' => isset($data['dosen_noHp']) && $data['dosen_noHp'] !== '' ? trim((string) $data['dosen_noHp']) : null,
            'prodi_id' => isset($data['prodi_id']) && $data['prodi_id'] !== '' ? (int) $data['prodi_id'] : null,
        ];

        try {
            $dosen = $this->userRepository->updateDosen($userId, $userData, $dosenData);
            Event::dispatch(new UserUpdated($dosen->user, $data));

            return [
                'success' => true,
                'message' => 'Data dosen berhasil diperbarui',
                'dosen_id' => $dosen->dosen_id,
                'user_id' => $dosen->user_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui dosen: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Create tendik user
     *
     * @param array $data
     * @return array
     */
    public function createTendik(array $data): array
    {
        $levelTendik = $this->resolveLevel('TDK', 'Tendik');
        if (!$levelTendik) {
            return $this->levelNotFoundResponse('Tendik');
        }

        $validation = $this->validatePersonPayload($data, [
            'username' => 'username',
            'password' => 'password',
            'tendik_nidn' => 'tendik_nidn',
            'tendik_nama' => 'tendik_nama',
        ], 'tendik');
        if ($validation) {
            return $validation;
        }

        $userData = [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => Hash::make((string) ($data['password'] ?? '')),
            'level_id' => $levelTendik->level_id,
        ];

        $tendikData = [
            'tendik_nidn' => trim((string) ($data['tendik_nidn'] ?? '')),
            'tendik_nama' => trim((string) ($data['tendik_nama'] ?? '')),
            'tendik_noHp' => isset($data['tendik_noHp']) && $data['tendik_noHp'] !== '' ? trim((string) $data['tendik_noHp']) : null,
        ];

        try {
            return DB::transaction(function () use ($userData, $tendikData): array {
                if ($this->userRepository->usernameExists($userData['username'])) {
                    return [
                        'success' => false,
                        'message' => 'Username sudah terdaftar',
                        'errors' => ['username' => 'Username sudah terdaftar']
                    ];
                }

                $tendik = $this->userRepository->createTendik($userData, $tendikData);
                Event::dispatch(new UserCreated($tendik->user));

                return [
                    'success' => true,
                    'message' => 'Data tendik berhasil ditambahkan',
                    'tendik_id' => $tendik->tendik_id,
                    'user_id' => $tendik->user_id
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan tendik: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Update tendik (m_user + m_tendik) sekaligus.
     *
     * updateUser() generik hanya menulis ke m_user, jadi field tendik
     * (tendik_nama, tendik_nidn, tendik_noHp) tidak boleh lewat situ.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateTendik(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        $userData = [];
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userRepository->usernameExists($data['username'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'Username sudah terdaftar',
                    'errors' => ['username' => 'Username sudah terdaftar']
                ];
            }
            $userData['username'] = trim((string) $data['username']);
        }
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make((string) $data['password']);
        }

        $tendikData = [
            'tendik_nama' => trim((string) ($data['tendik_nama'] ?? '')),
            'tendik_nidn' => trim((string) ($data['tendik_nidn'] ?? '')),
            'tendik_noHp' => isset($data['tendik_noHp']) && $data['tendik_noHp'] !== '' ? trim((string) $data['tendik_noHp']) : null,
        ];

        try {
            $tendik = $this->userRepository->updateTendik($userId, $userData, $tendikData);
            Event::dispatch(new UserUpdated($tendik->user, $data));

            return [
                'success' => true,
                'message' => 'Data tendik berhasil diperbarui',
                'tendik_id' => $tendik->tendik_id,
                'user_id' => $tendik->user_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui tendik: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Create mahasiswa user
     *
     * @param array $data
     * @return array
     */
    public function createMahasiswa(array $data): array
    {
        $levelMahasiswa = $this->resolveLevel('MHS', 'Mahasiswa');
        if (!$levelMahasiswa) {
            return $this->levelNotFoundResponse('Mahasiswa');
        }

        $validation = $this->validatePersonPayload($data, [
            'username' => 'username',
            'password' => 'password',
            'mahasiswa_nim' => 'mahasiswa_nim',
            'mahasiswa_nama' => 'mahasiswa_nama',
            'prodi_id' => 'prodi_id',
        ], 'mahasiswa');
        if ($validation) {
            return $validation;
        }

        $userData = [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => Hash::make((string) ($data['password'] ?? '')),
            'level_id' => $levelMahasiswa->level_id,
        ];

        $mahasiswaData = [
            'mahasiswa_nim' => trim((string) ($data['mahasiswa_nim'] ?? '')),
            'mahasiswa_nama' => trim((string) ($data['mahasiswa_nama'] ?? '')),
            'mahasiswa_noHp' => isset($data['mahasiswa_noHp']) && $data['mahasiswa_noHp'] !== '' ? trim((string) $data['mahasiswa_noHp']) : null,
            'prodi_id' => (int) ($data['prodi_id'] ?? 0),
            'kelas_id' => isset($data['kelas_id']) && $data['kelas_id'] !== '' ? (int) $data['kelas_id'] : null,
            'organisasi_ids' => array_map('intval', (array) ($data['organisasi_ids'] ?? [])),
        ];

        try {
            return DB::transaction(function () use ($userData, $mahasiswaData): array {
                if ($this->userRepository->usernameExists($userData['username'])) {
                    return [
                        'success' => false,
                        'message' => 'Username sudah terdaftar',
                        'errors' => ['username' => 'Username sudah terdaftar']
                    ];
                }

                $mahasiswa = $this->userRepository->createMahasiswa($userData, $mahasiswaData);
                Event::dispatch(new UserCreated($mahasiswa->user));

                return [
                    'success' => true,
                    'message' => 'Data mahasiswa berhasil ditambahkan',
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                    'user_id' => $mahasiswa->user_id
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan mahasiswa: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Update mahasiswa (m_user + m_mahasiswa + keanggotaan organisasi) sekaligus.
     *
     * updateUser() generik hanya menulis ke m_user, jadi field mahasiswa
     * (prodi_id, kelas_id, organisasi_ids, dst.) tidak boleh lewat situ.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateMahasiswa(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan',
                'errors' => ['user_id' => 'User tidak ditemukan']
            ];
        }

        $userData = [];
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if ($this->userRepository->usernameExists($data['username'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'Username sudah terdaftar',
                    'errors' => ['username' => 'Username sudah terdaftar']
                ];
            }
            $userData['username'] = trim((string) $data['username']);
        }
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make((string) $data['password']);
        }

        $mahasiswaData = [
            'mahasiswa_nama' => trim((string) ($data['mahasiswa_nama'] ?? '')),
            'mahasiswa_nim' => trim((string) ($data['mahasiswa_nim'] ?? '')),
            'mahasiswa_noHp' => isset($data['mahasiswa_noHp']) && $data['mahasiswa_noHp'] !== '' ? trim((string) $data['mahasiswa_noHp']) : null,
            'prodi_id' => (int) ($data['prodi_id'] ?? 0),
            'kelas_id' => isset($data['kelas_id']) && $data['kelas_id'] !== '' ? (int) $data['kelas_id'] : null,
            'organisasi_ids' => array_map('intval', (array) ($data['organisasi_ids'] ?? [])),
        ];

        try {
            $mahasiswa = $this->userRepository->updateMahasiswa($userId, $userData, $mahasiswaData);
            Event::dispatch(new UserUpdated($mahasiswa->user, $data));

            return [
                'success' => true,
                'message' => 'Data mahasiswa berhasil diperbarui',
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'user_id' => $mahasiswa->user_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui mahasiswa: ' . $e->getMessage(),
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }

    /**
     * Validate required fields for a payload.
     *
     * @param array $data
     * @param array $requiredFields
     * @return array|null
     */
    protected function validateRequiredFields(array $data, array $requiredFields): ?array
    {
        foreach ($requiredFields as $field) {
            $value = $data[$field] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
                return [
                    'success' => false,
                    'message' => "Field {$field} wajib diisi",
                    'errors' => [$field => "Field {$field} wajib diisi"]
                ];
            }
        }

        return null;
    }

    /**
     * Validate that a username is unique.
     *
     * @param string $username
     * @return array|null
     */
    protected function validateUniqueUsername(string $username): ?array
    {
        if ($this->userRepository->usernameExists($username)) {
            return [
                'success' => false,
                'message' => 'Username sudah terdaftar',
                'errors' => ['username' => 'Username sudah terdaftar']
            ];
        }

        return null;
    }

    /**
     * Normalize user payload values.
     *
     * @param array $data
     * @return array
     */
    protected function normalizeUserPayload(array $data): array
    {
        return [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => trim((string) ($data['password'] ?? '')),
            'level_id' => (int) ($data['level_id'] ?? 0),
        ];
    }

    /**
     * Validate the common person-specific payload fields.
     *
     * @param array $data
     * @param array $requiredFields
     * @param string $prefix
     * @return array|null
     */
    protected function validatePersonPayload(array $data, array $requiredFields, string $prefix): ?array
    {
        $requiredValidation = $this->validateRequiredFields($data, array_values($requiredFields));
        if ($requiredValidation) {
            return $requiredValidation;
        }

        $usernameValidation = $this->validateUniqueUsername(trim((string) ($data['username'] ?? '')));
        if ($usernameValidation) {
            return $usernameValidation;
        }

        return null;
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
     * Create a level-not-found response.
     *
     * @param string $label
     * @return array
     */
    protected function levelNotFoundResponse(string $label): array
    {
        return [
            'success' => false,
            'message' => "Level {$label} tidak ditemukan",
            'errors' => ['level' => "Level {$label} tidak ditemukan"]
        ];
    }

    /**
     * Find user by ID with relations
     *
     * @param int $userId
     * @return \App\Models\UserModel|null
     */
    public function findUserById(int $userId): ?UserModel
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15)
    {
        return $this->userRepository->getAllPaginated($perPage);
    }
}
