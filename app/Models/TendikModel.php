<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\DTO\ProfileDTO;

class TendikModel extends Model
{
    use HasFactory;
    protected $table= 'm_tendik'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'tendik_id';
    protected $fillable = ['user_id', 'tendik_nama','tendik_nidn','tendik_noHp','created_at','updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }
    
    /**
     * Validate tendik data
     * 
     * Validates: Requirement 17.3
     * 
     * @return array Array of validation errors, empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        // Validate NIDN/NIP format (should be numeric and length 10-18)
        if (!preg_match('/^\d{10,18}$/', $this->tendik_nidn)) {
            $errors['tendik_nidn'] = 'NIDN/NIP harus berupa angka dengan panjang 10-18 digit';
        }
        
        // Validate name (should not be empty)
        if (empty(trim($this->tendik_nama))) {
            $errors['tendik_nama'] = 'Nama tendik tidak boleh kosong';
        }
        
        // Validate phone number format (Indonesian phone number)
        if (!empty($this->tendik_noHp) && !preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $this->tendik_noHp)) {
            $errors['tendik_noHp'] = 'Format nomor telepon tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Get tendik profile as DTO
     * 
     * Validates: Requirement 17.1
     * 
     * @return ProfileDTO Tendik profile information
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
            $this->tendik_nama,
            'TDK',
            'Tenaga Kependidikan',
            $this->created_at ?? now(),
            $this->updated_at ?? now(),
            $this->tendik_nidn,
            $this->tendik_noHp
        );
    }
    
    /**
     * Get formatted display information
     * 
     * @return array Formatted tendik information
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->tendik_id,
            'name' => $this->tendik_nama,
            'nidn' => $this->tendik_nidn,
            'phone' => $this->tendik_noHp,
            'user' => $this->user ? [
                'username' => $this->user->username,
                'role' => $this->user->getRoleName()
            ] : null
        ];
    }
    
    /**
     * Check if tendik can perform administrative task
     * 
     * @param string $task Task identifier
     * @return bool True if tendik can perform the task
     */
    public function canPerformTask(string $task): bool
    {
        // Implementation would check tendik's permissions/role
        // For now, return true for basic administrative tasks
        $administrativeTasks = ['data_entry', 'report_generation', 'document_processing'];
        return in_array($task, $administrativeTasks);
    }
    
    /**
     * Get tendik's department/unit assignment
     * 
     * @return string|null Department/unit name if assigned
     */
    public function getDepartment(): ?string
    {
        // This would typically come from a separate assignment table
        // For now, return null as default
        return null;
    }
}
