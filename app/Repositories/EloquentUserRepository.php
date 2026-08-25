<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\TendikModel;
use App\Models\MahasiswaModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @var UserModel
     */
    protected $user;

    /**
     * @var AdminModel
     */
    protected $admin;

    /**
     * @var DosenModel
     */
    protected $dosen;

    /**
     * @var TendikModel
     */
    protected $tendik;

    /**
     * @var MahasiswaModel
     */
    protected $mahasiswa;

    /**
     * Constructor
     *
     * @param UserModel $user
     * @param AdminModel $admin
     * @param DosenModel $dosen
     * @param TendikModel $tendik
     * @param MahasiswaModel $mahasiswa
     */
    public function __construct(
        UserModel $user,
        AdminModel $admin,
        DosenModel $dosen,
        TendikModel $tendik,
        MahasiswaModel $mahasiswa
    ) {
        $this->user = $user;
        $this->admin = $admin;
        $this->dosen = $dosen;
        $this->tendik = $tendik;
        $this->mahasiswa = $mahasiswa;
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return UserModel|null
     */
    public function findByUsername(string $username): ?UserModel
    {
        return $this->user->with(['level'])->where('username', $username)->first();
    }

    /**
     * Find user by ID with relations
     *
     * @param int $userId
     * @return UserModel|null
     */
    public function findById(int $userId): ?UserModel
    {
        return $this->user->with(['level'])->find($userId);
    }

    /**
     * Create new user with transaction
     *
     * @param array $data
     * @return UserModel
     */
    public function create(array $data): UserModel
    {
        return DB::transaction(function () use ($data) {
            return $this->user->create($data);
        });
    }

    /**
     * Update user data
     *
     * @param int $userId
     * @param array $data
     * @return UserModel
     */
    public function update(int $userId, array $data): UserModel
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = $this->user->findOrFail($userId);
            $user->update($data);
            
            return $user;
        });
    }

    /**
     * Delete user by ID
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        return DB::transaction(function () use ($userId) {
            $user = $this->user->findOrFail($userId);

            // Resolve each HasOne relation to its model (or null if no related record),
            // then delete only when a related record actually exists.
            // NOTE: relation() always returns a HasOne builder (never null),
            // so the nullsafe must be applied to first()'s result, not the builder.
            $user->admin()->first()?->delete();
            $user->dosen()->first()?->delete();
            $user->tendik()->first()?->delete();
            $user->mahasiswa()->first()?->delete();

            return $user->delete();
        });
    }

    /**
     * Find user by level
     *
     * @param int $levelId
     * @return Collection
     */
    public function findByLevel(int $levelId): Collection
    {
        return $this->user->with(['level'])->where('level_id', $levelId)->get();
    }

    /**
     * Get all users with eager loading
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->user->with(['level'])->get();
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->user->with(['level'])->paginate($perPage);
    }

    /**
     * Check if username exists
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $query = $this->user->where('username', $username);
        
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        
        return $query->exists();
    }

    /**
     * Create admin with user
     *
     * @param array $userData
     * @param array $adminData
     * @return AdminModel
     */
    public function createAdmin(array $userData, array $adminData): AdminModel
    {
        return DB::transaction(function () use ($userData, $adminData) {
            $user = $this->user->create($userData);

            $adminData['user_id'] = $user->user_id;

            return $this->admin->create($adminData);
        });
    }

    /**
     * Update admin (m_user + m_admin) sekaligus — lihat catatan di updateMahasiswa().
     *
     * @param int $userId
     * @param array $userData
     * @param array $adminData
     * @return AdminModel
     */
    public function updateAdmin(int $userId, array $userData, array $adminData): AdminModel
    {
        return DB::transaction(function () use ($userId, $userData, $adminData) {
            $user = $this->user->findOrFail($userId);
            if (!empty($userData)) {
                $user->update($userData);
            }

            $admin = $this->admin->where('user_id', $userId)->firstOrFail();
            if (!empty($adminData)) {
                $admin->update($adminData);
            }

            return $admin;
        });
    }

    /**
     * Create dosen with user
     *
     * @param array $userData
     * @param array $dosenData
     * @return DosenModel
     */
    public function createDosen(array $userData, array $dosenData): DosenModel
    {
        return DB::transaction(function () use ($userData, $dosenData) {
            $user = $this->user->create($userData);

            $dosenData['user_id'] = $user->user_id;

            return $this->dosen->create($dosenData);
        });
    }

    /**
     * Update dosen (m_user + m_dosen) sekaligus — lihat catatan di updateMahasiswa().
     *
     * @param int $userId
     * @param array $userData
     * @param array $dosenData
     * @return DosenModel
     */
    public function updateDosen(int $userId, array $userData, array $dosenData): DosenModel
    {
        return DB::transaction(function () use ($userId, $userData, $dosenData) {
            $user = $this->user->findOrFail($userId);
            if (!empty($userData)) {
                $user->update($userData);
            }

            $dosen = $this->dosen->where('user_id', $userId)->firstOrFail();
            if (!empty($dosenData)) {
                $dosen->update($dosenData);
            }

            return $dosen;
        });
    }

    /**
     * Create tendik with user
     *
     * @param array $userData
     * @param array $tendikData
     * @return TendikModel
     */
    public function createTendik(array $userData, array $tendikData): TendikModel
    {
        return DB::transaction(function () use ($userData, $tendikData) {
            $user = $this->user->create($userData);

            $tendikData['user_id'] = $user->user_id;

            return $this->tendik->create($tendikData);
        });
    }

    /**
     * Update tendik (m_user + m_tendik) sekaligus — lihat catatan di updateMahasiswa().
     *
     * @param int $userId
     * @param array $userData
     * @param array $tendikData
     * @return TendikModel
     */
    public function updateTendik(int $userId, array $userData, array $tendikData): TendikModel
    {
        return DB::transaction(function () use ($userId, $userData, $tendikData) {
            $user = $this->user->findOrFail($userId);
            if (!empty($userData)) {
                $user->update($userData);
            }

            $tendik = $this->tendik->where('user_id', $userId)->firstOrFail();
            if (!empty($tendikData)) {
                $tendik->update($tendikData);
            }

            return $tendik;
        });
    }

    /**
     * Create mahasiswa with user
     *
     * @param array $userData
     * @param array $mahasiswaData
     * @return MahasiswaModel
     */
    public function createMahasiswa(array $userData, array $mahasiswaData): MahasiswaModel
    {
        return DB::transaction(function () use ($userData, $mahasiswaData) {
            $organisasiIds = $mahasiswaData['organisasi_ids'] ?? null;
            unset($mahasiswaData['organisasi_ids']);

            $user = $this->user->create($userData);

            $mahasiswaData['user_id'] = $user->user_id;

            $mahasiswa = $this->mahasiswa->create($mahasiswaData);

            if ($organisasiIds !== null) {
                $mahasiswa->organisasis()->sync($organisasiIds);
            }

            return $mahasiswa;
        });
    }

    /**
     * Update mahasiswa (m_user + m_mahasiswa + keanggotaan organisasi) sekaligus.
     *
     * updateUser() generik cuma menulis ke m_user (username/password/level_id) —
     * data mahasiswa (prodi_id, kelas_id, dst.) diam-diam tidak pernah tersimpan
     * karena tidak ada di $fillable UserModel. Method ini yang jadi jalur benar
     * untuk edit mahasiswa, dipanggil dari MahasiswaController::update_ajax.
     *
     * @param int $userId
     * @param array $userData
     * @param array $mahasiswaData
     * @return MahasiswaModel
     */
    public function updateMahasiswa(int $userId, array $userData, array $mahasiswaData): MahasiswaModel
    {
        return DB::transaction(function () use ($userId, $userData, $mahasiswaData) {
            $organisasiIds = $mahasiswaData['organisasi_ids'] ?? null;
            unset($mahasiswaData['organisasi_ids']);

            $user = $this->user->findOrFail($userId);
            if (!empty($userData)) {
                $user->update($userData);
            }

            $mahasiswa = $this->mahasiswa->where('user_id', $userId)->firstOrFail();
            if (!empty($mahasiswaData)) {
                $mahasiswa->update($mahasiswaData);
            }

            if ($organisasiIds !== null) {
                $mahasiswa->organisasis()->sync($organisasiIds);
            }

            return $mahasiswa;
        });
    }
}
