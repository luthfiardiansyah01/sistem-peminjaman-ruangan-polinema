<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\DTO\ProfileDTO;

class MahasiswaModel extends Model
{
    use HasFactory;
    protected $table= 'm_mahasiswa'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'mahasiswa_id';
    protected $fillable = ['user_id', 'prodi_id', 'kelas_id', 'mahasiswa_nama','mahasiswa_nim','mahasiswa_noHp','created_at','updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProdiModel::class, 'prodi_id', 'prodi_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasModel::class, 'kelas_id', 'kelas_id');
    }

    public function organisasis(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganisasiModel::class,
            'm_mahasiswa_organisasi',
            'mahasiswa_id',
            'organisasi_id'
        )->withTimestamps();
    }
    
    /**
     * Validate mahasiswa data
     * 
     * Validates: Requirement 17.3
     * 
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate NIM format (should be numeric and length 8-12)
        if (!preg_match('/^\d{8,12}$/', $this->mahasiswa_nim)) {
            $errors['mahasiswa_nim'] = 'NIM harus berupa angka dengan panjang 8-12 digit';
        }
        
        // Validate name (should not be empty)
        if (empty(trim($this->mahasiswa_nama))) {
            $errors['mahasiswa_nama'] = 'Nama mahasiswa tidak boleh kosong';
        }
        
        // Validate phone number format (Indonesian phone number)
        if (!empty($this->mahasiswa_noHp) && !preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $this->mahasiswa_noHp)) {
            $errors['mahasiswa_noHp'] = 'Format nomor telepon tidak valid';
        }
        
        // Validate prodi_id exists if provided
        if (empty($this->prodi_id)) {
            $errors['prodi_id'] = 'Program studi harus dipilih';
        }
        
        // Validate kelas_id exists if provided
        if (empty($this->kelas_id)) {
            $errors['kelas_id'] = 'Kelas harus dipilih';
        }
        
        return $errors;
    }
    
    /**
     * Get mahasiswa profile as DTO
     * 
     * Validates: Requirement 17.1
     * 
     * @return ProfileDTO Mahasiswa profile information
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
            $this->mahasiswa_nama,
            'MHS',
            'Mahasiswa',
            $this->created_at ?? now(),
            $this->updated_at ?? now(),
            $this->mahasiswa_nim,
            $this->mahasiswa_noHp,
            $this->prodi ? $this->prodi->prodi_nama : null,
            $this->kelas ? $this->kelas->kelas_nama : null
        );
    }
    
    /**
     * Check if mahasiswa is enrolled in specific program study
     * 
     * @param int|ProdiModel $prodi Program study ID or model
     * @return bool True if mahasiswa is enrolled in the prodi
     */
    public function isEnrolledInProdi($prodi): bool
    {
        $prodiId = $prodi instanceof ProdiModel ? $prodi->prodi_id : $prodi;
        return $this->prodi_id == $prodiId;
    }
    
    /**
     * Check if mahasiswa belongs to specific class
     * 
     * @param int|KelasModel $kelas Class ID or model
     * @return bool True if mahasiswa belongs to the class
     */
    public function belongsToClass($kelas): bool
    {
        $kelasId = $kelas instanceof KelasModel ? $kelas->kelas_id : $kelas;
        return $this->kelas_id == $kelasId;
    }
    
    /**
     * Get formatted display information
     * 
     * @return array Formatted mahasiswa information
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->mahasiswa_id,
            'name' => $this->mahasiswa_nama,
            'nim' => $this->mahasiswa_nim,
            'phone' => $this->mahasiswa_noHp,
            'prodi' => $this->prodi ? $this->prodi->prodi_nama : null,
            'kelas' => $this->kelas ? $this->kelas->kelas_nama : null,
            'user' => $this->user ? [
                'username' => $this->user->username,
                'role' => $this->user->getRoleName()
            ] : null
        ];
    }
    
    /**
     * Get mahasiswa's academic status
     * 
     * @return string Academic status (active, graduated, dropout, etc.)
     */
    public function getAcademicStatus(): string
    {
        // This would typically check against academic records
        // For now, return 'active' as default
        return 'active';
    }
}
