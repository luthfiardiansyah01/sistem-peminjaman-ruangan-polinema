<?php

namespace App\Services\Interfaces;

use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;

interface UserServiceInterface
{
    /**
     * Create a new user with transaction support
     *
     * @param array $data
     * @return array
     */
    public function createUser(array $data): array;

    /**
     * Update user data
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateUser(int $userId, array $data): array;

    /**
     * Delete user by ID
     *
     * @param int $userId
     * @return array
     */
    public function deleteUser(int $userId): array;

    /**
     * Create admin user
     *
     * @param array $data
     * @return array
     */
    public function createAdmin(array $data): array;

    /**
     * Update admin (m_user + m_admin) sekaligus
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateAdmin(int $userId, array $data): array;

    /**
     * Create dosen user
     *
     * @param array $data
     * @return array
     */
    public function createDosen(array $data): array;

    /**
     * Update dosen (m_user + m_dosen) sekaligus
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateDosen(int $userId, array $data): array;

    /**
     * Create tendik user
     *
     * @param array $data
     * @return array
     */
    public function createTendik(array $data): array;

    /**
     * Update tendik (m_user + m_tendik) sekaligus
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateTendik(int $userId, array $data): array;

    /**
     * Create mahasiswa user
     *
     * @param array $data
     * @return array
     */
    public function createMahasiswa(array $data): array;

    /**
     * Update mahasiswa (m_user + m_mahasiswa + keanggotaan organisasi) sekaligus
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateMahasiswa(int $userId, array $data): array;

    /**
     * Find user by ID with relations
     *
     * @param int $userId
     * @return UserModel|null
     */
    public function findUserById(int $userId): ?UserModel;

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15);
}
