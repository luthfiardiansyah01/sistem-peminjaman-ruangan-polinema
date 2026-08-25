<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\DTO\ProfileDTO;

class UserModel extends Authenticatable implements JWTSubject
{
    use HasFactory;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $table= 'm_user'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'user_id';
    protected $fillable = ['level_id', 'username', 'password', 'created_at', 'updated_at'];
    protected $hidden   = ['password'];

    protected $casts    = ['password' => 'hashed'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(LevelModel::class, 'level_id', 'level_id');
    }

    //mendapatkan nama role
    public function getRoleName(): string
    {
        // Menggunakan optional() agar aman jika relasi level bernilai null
        return optional($this->level)->level_nama ?? '';
    }
    
    /**
     * Check if user has a specific role
     * 
     * Validates: Requirement 9.2
     * 
     * @param string $role Expected role code (ADM, DSN, TDK, MHS)
     * @return bool True if user has the expected role, false otherwise
     */
    public function hasRole(string $role): bool
    {
        // Menggunakan optional() agar aman jika relasi level bernilai null
        return optional($this->level)->level_kode == $role;
    }
    
    /**
     * Assign a new role to the user by directly providing the level_id.
     *
     * Validates: Requirement 9.1
     *
     * This method intentionally accepts a resolved `level_id` instead of a
     * role code string, keeping the Model free of Service-layer dependencies.
     * The caller (Service layer) is responsible for resolving the level code
     * to its corresponding `level_id` before calling this method.
     *
     * @param int $levelId The resolved level_id to assign
     * @return bool True if role assignment was successful, false otherwise
     */
    public function assignRole(int $levelId): bool
    {
        try {
            $this->level_id = $levelId;
            $saved = $this->save();

            if ($saved) {
                // Clear the relationship cache to ensure fresh data
                unset($this->relations['level']);
            }

            return $saved;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    //mendapatkan kode role
    public function getRole(): string
    {
        // Menggunakan optional() agar aman jika relasi level bernilai null. Mengembalikan string kosong jika level tidak ada.
        return optional($this->level)->level_kode ?? '';
    }

    /**
     * Get user display name
     *
     * Validates: Requirement 9.3
     *
     * @return string Display name based on person type, or '-' if no person data found
     *
     * @see UserModel::$with — callers should eager-load 'dosen', 'admin',
     *      'tendik', and 'mahasiswa' when accessing this method in a loop
     *      to avoid N+1 queries.
     */
    public function getDisplayName(): string
    {
        if ($this->dosen) {
            return $this->dosen->dosen_nama;
        } elseif ($this->admin) {
            return $this->admin->admin_nama;
        } elseif ($this->tendik) {
            return $this->tendik->tendik_nama;
        } elseif ($this->mahasiswa) {
            return $this->mahasiswa->mahasiswa_nama;
        } else {
            return '-';
        }
    }
    
    /**
     * Get user profile as DTO
     * 
     * Validates: Requirement 9.4
     * 
     * @return ProfileDTO User profile information
     */
    public function getProfile(): ProfileDTO
    {
        return ProfileDTO::fromUserModel($this);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(AdminModel::class, 'user_id', 'user_id');
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(DosenModel::class, 'user_id', 'user_id');
    }

    public function tendik(): HasOne
    {
        return $this->hasOne(TendikModel::class, 'user_id', 'user_id');
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(MahasiswaModel::class, 'user_id', 'user_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalModel::class, 'user_id', 'user_id');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanModel::class, 'user_id', 'user_id');
    }

    public function jabatanApprovals(): HasMany
    {
        return $this->hasMany(JabatanApprovalModel::class, 'user_id', 'user_id');
    }
    
    /**
     * @deprecated Use getDisplayName() instead for consistency with requirements
     */
    public function getNamaPembuatAttribute()
    {
        return $this->getDisplayName();
    }
}
