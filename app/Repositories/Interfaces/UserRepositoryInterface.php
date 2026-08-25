<?php

namespace App\Repositories\Interfaces;

use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    /**
     * Find user by username
     *
     * @param string $username
     * @return UserModel|null
     */
    public function findByUsername(string $username): ?UserModel;

    /**
     * Find user by ID with relations
     *
     * @param int $userId
     * @return UserModel|null
     */
    public function findById(int $userId): ?UserModel;

    /**
     * Create new user with transaction
     *
     * @param array $data
     * @return UserModel
     */
    public function create(array $data): UserModel;

    /**
     * Update user data
     *
     * @param int $userId
     * @param array $data
     * @return UserModel
     */
    public function update(int $userId, array $data): UserModel;

    /**
     * Delete user by ID
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool;

    /**
     * Find user by level
     *
     * @param int $levelId
     * @return Collection
     */
    public function findByLevel(int $levelId): Collection;

    /**
     * Get all users with eager loading
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15);

    /**
     * Check if username exists
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool;

    /**
     * Create admin with user
     *
     * @param array $userData
     * @param array $adminData
     * @return AdminModel
     */
    public function createAdmin(array $userData, array $adminData): AdminModel;

    /**
     * Update admin (m_user + m_admin) sekaligus
     *
     * @param int $userId
     * @param array $userData
     * @param array $adminData
     * @return AdminModel
     */
    public function updateAdmin(int $userId, array $userData, array $adminData): AdminModel;

    /**
     * Create dosen with user
     *
     * @param array $userData
     * @param array $dosenData
     * @return DosenModel
     */
    public function createDosen(array $userData, array $dosenData): DosenModel;

    /**
     * Update dosen (m_user + m_dosen) sekaligus
     *
     * @param int $userId
     * @param array $userData
     * @param array $dosenData
     * @return DosenModel
     */
    public function updateDosen(int $userId, array $userData, array $dosenData): DosenModel;

    /**
     * Create tendik with user
     *
     * @param array $userData
     * @param array $tendikData
     * @return TendikModel
     */
    public function createTendik(array $userData, array $tendikData): TendikModel;

    /**
     * Update tendik (m_user + m_tendik) sekaligus
     *
     * @param int $userId
     * @param array $userData
     * @param array $tendikData
     * @return TendikModel
     */
    public function updateTendik(int $userId, array $userData, array $tendikData): TendikModel;

    /**
     * Create mahasiswa with user
     *
     * @param array $userData
     * @param array $mahasiswaData
     * @return MahasiswaModel
     */
    public function createMahasiswa(array $userData, array $mahasiswaData): MahasiswaModel;

    /**
     * Update mahasiswa (m_user + m_mahasiswa + keanggotaan organisasi) sekaligus
     *
     * @param int $userId
     * @param array $userData
     * @param array $mahasiswaData
     * @return MahasiswaModel
     */
    public function updateMahasiswa(int $userId, array $userData, array $mahasiswaData): MahasiswaModel;
}
