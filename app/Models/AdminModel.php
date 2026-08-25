<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\DTO\ProfileDTO;

class AdminModel extends Model
{
    use HasFactory;
    protected $table= 'm_admin'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'admin_id';
    protected $fillable = ['user_id', 'prodi_id', 'admin_nama','admin_nidn','admin_noHp','created_at','updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }
    
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProdiModel::class, 'prodi_id', 'prodi_id');
    }
    
    /**
     * Validate admin data
     * 
     * Validates: Requirement 17.3
     * 
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate NIDN format (should be numeric and length 10-16)
        if (!preg_match('/^\d{10,16}$/', $this->admin_nidn)) {
            $errors['admin_nidn'] = 'NIDN harus berupa angka dengan panjang 10-16 digit';
        }
        
        // Validate name (should not be empty)
        if (empty(trim($this->admin_nama))) {
            $errors['admin_nama'] = 'Nama admin tidak boleh kosong';
        }
        
        // Validate phone number format (Indonesian phone number)
        if (!empty($this->admin_noHp) && !preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $this->admin_noHp)) {
            $errors['admin_noHp'] = 'Format nomor telepon tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Get admin profile as DTO
     * 
     * Validates: Requirement 17.1
     * 
     * @return ProfileDTO Admin profile information
     */
    public function getProfile(): ProfileDTO
    {
        if ($this->user) {
            return $this->user->getProfile();
        }
        
        // Fallback if user relation is not loaded
        return new ProfileDTO(
            $this->user_id,
            '', // username not available
            $this->admin_nama,
            'ADM',
            'Administrator',
            $this->created_at ?? now(),
            $this->updated_at ?? now(),
            $this->admin_nidn,
            $this->admin_noHp,
            $this->prodi ? $this->prodi->prodi_nama : null
        );
    }
    
    /**
     * Check if admin can manage specific program study
     * 
     * @param int|ProdiModel $prodi Program study ID or model
     * @return bool True if admin can manage the prodi
     */
    public function canManageProdi($prodi): bool
    {
        $prodiId = $prodi instanceof ProdiModel ? $prodi->prodi_id : $prodi;
        
        // Admin without specific prodi assignment can manage all prodies
        if (empty($this->prodi_id)) {
            return true;
        }
        
        // Admin with prodi assignment can only manage their assigned prodi
        return $this->prodi_id == $prodiId;
    }
    
    /**
     * Get formatted display information
     * 
     * @return array Formatted admin information
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->admin_id,
            'name' => $this->admin_nama,
            'nidn' => $this->admin_nidn,
            'phone' => $this->admin_noHp,
            'prodi' => $this->prodi ? $this->prodi->prodi_nama : null,
            'user' => $this->user ? [
                'username' => $this->user->username,
                'role' => $this->user->getRoleName()
            ] : null
        ];
    }
}
