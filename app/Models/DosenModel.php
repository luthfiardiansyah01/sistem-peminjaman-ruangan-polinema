<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\DTO\ProfileDTO;

class DosenModel extends Model
{
    use HasFactory;
    protected $table= 'm_dosen'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'dosen_id';
    protected $fillable = ['user_id', 'prodi_id', 'dosen_nama','dosen_nip','dosen_nidn','dosen_noHp','created_at','updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }
    
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(ProdiModel::class, 'prodi_id', 'prodi_id');
    }
    
    /**
     * Validate dosen data
     * 
     * Validates: Requirement 17.3
     * 
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate NIP/NIDN: dosen bisa punya salah satu atau keduanya sekaligus (klien
        // revisi ke-2 poin 3/4 konfirmasi) — minimal satu wajib diisi. Panjang digit final
        // sudah dikonfirmasi klien: NIDN persis 10 digit, NIP persis 18 digit.
        if (empty($this->dosen_nip) && empty($this->dosen_nidn)) {
            $errors['dosen_nidn'] = 'NIP atau NIDN wajib diisi salah satu';
        }
        if (!empty($this->dosen_nip) && !preg_match('/^\d{18}$/', $this->dosen_nip)) {
            $errors['dosen_nip'] = 'NIP harus berupa angka 18 digit';
        }
        if (!empty($this->dosen_nidn) && !preg_match('/^\d{10}$/', $this->dosen_nidn)) {
            $errors['dosen_nidn'] = 'NIDN harus berupa angka 10 digit';
        }
        
        // Validate name (should not be empty)
        if (empty(trim($this->dosen_nama))) {
            $errors['dosen_nama'] = 'Nama dosen tidak boleh kosong';
        }
        
        // Validate phone number format (Indonesian phone number)
        if (!empty($this->dosen_noHp) && !preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $this->dosen_noHp)) {
            $errors['dosen_noHp'] = 'Format nomor telepon tidak valid';
        }
        
        // Validate prodi_id exists if provided
        if (empty($this->prodi_id)) {
            $errors['prodi_id'] = 'Program studi harus dipilih';
        }
        
        return $errors;
    }
    
    /**
     * Get dosen profile as DTO
     * 
     * Validates: Requirement 17.1
     * 
     * @return ProfileDTO Dosen profile information
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
            $this->dosen_nama,
            'DSN',
            'Dosen',
            $this->created_at ?? now(),
            $this->updated_at ?? now(),
            $this->identitasUtama(),
            $this->dosen_noHp,
            $this->prodi ? $this->prodi->prodi_nama : null
        );
    }
    
    /**
     * Identitas utama dosen: NIP diprioritaskan kalau ada, fallback ke NIDN kalau tidak
     * (klien revisi ke-2 poin 4 konfirmasi — dosen bisa punya keduanya sekaligus).
     *
     * @return string|null
     */
    public function identitasUtama(): ?string
    {
        return $this->dosen_nip ?: $this->dosen_nidn;
    }

    /**
     * Check if dosen teaches specific program study
     * 
     * @param int|ProdiModel $prodi Program study ID or model
     * @return bool True if dosen teaches in the prodi
     */
    public function teachesInProdi($prodi): bool
    {
        $prodiId = $prodi instanceof ProdiModel ? $prodi->prodi_id : $prodi;
        return $this->prodi_id == $prodiId;
    }
    
    /**
     * Get formatted display information
     * 
     * @return array Formatted dosen information
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->dosen_id,
            'name' => $this->dosen_nama,
            'nidn' => $this->identitasUtama(),
            'phone' => $this->dosen_noHp,
            'prodi' => $this->prodi ? $this->prodi->prodi_nama : null,
            'user' => $this->user ? [
                'username' => $this->user->username,
                'role' => $this->user->getRoleName()
            ] : null
        ];
    }
    
    /**
     * Get dosen's availability status for scheduling
     * 
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @return bool True if dosen is available
     */
    public function isAvailable(string $date, string $time): bool
    {
        // Implementation would check against schedule database
        // For now, return true as default
        return true;
    }
}
