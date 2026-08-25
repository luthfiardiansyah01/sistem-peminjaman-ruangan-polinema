<?php

namespace App\DTO;

/**
 * Data Transfer Object for updating users
 * 
 * This DTO encapsulates user update data with validation and sanitization
 * Supports partial updates and password change validation
 * 
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4, 19.1, 19.2, 19.3, 19.4, 30.1
 * 
 * @package App\DTO
 */
class UpdateUserDTO
{
    /**
     * @var string|null New username
     */
    public ?string $username;
    
    /**
     * @var string|null Current password (for verification)
     */
    public ?string $currentPassword;
    
    /**
     * @var string|null New password
     */
    public ?string $newPassword;
    
    /**
     * @var string|null Password confirmation
     */
    public ?string $passwordConfirmation;
    
    /**
     * @var string|null Name (nama)
     */
    public ?string $name;
    
    /**
     * @var string|null Phone number
     */
    public ?string $phoneNumber;
    
    /**
     * @var string|null Email
     */
    public ?string $email;
    
    /**
     * @var int|null Program study ID
     */
    public ?int $prodiId;
    
    /**
     * @var int|null Class ID (for Mahasiswa only)
     */
    public ?int $kelasId;
    
    /**
     * @var string User type (ADM, DSN, TDK, MHS) - for validation context
     */
    public string $userType;
    
    /**
     * @var bool Whether to perform partial update (allow null values)
     */
    public bool $partialUpdate;
    
    /**
     * Constructor
     * 
     * @param string|null $username
     * @param string|null $currentPassword
     * @param string|null $newPassword
     * @param string|null $passwordConfirmation
     * @param string|null $name
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param int|null $prodiId
     * @param int|null $kelasId
     * @param string $userType
     * @param bool $partialUpdate
     */
    public function __construct(
        ?string $username = null,
        ?string $currentPassword = null,
        ?string $newPassword = null,
        ?string $passwordConfirmation = null,
        ?string $name = null,
        ?string $phoneNumber = null,
        ?string $email = null,
        ?int $prodiId = null,
        ?int $kelasId = null,
        string $userType = '',
        bool $partialUpdate = true
    ) {
        $this->username = $username;
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
        $this->passwordConfirmation = $passwordConfirmation;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
        $this->prodiId = $prodiId;
        $this->kelasId = $kelasId;
        $this->userType = $userType;
        $this->partialUpdate = $partialUpdate;
    }
    
    /**
     * Create UpdateUserDTO from array
     * 
     * @param array $data
     * @param string $userType
     * @param bool $partialUpdate
     * @return self
     */
    public static function fromArray(array $data, string $userType = '', bool $partialUpdate = true): self
    {
        return new self(
            $data['username'] ?? null,
            $data['current_password'] ?? null,
            $data['new_password'] ?? $data['password'] ?? null,
            $data['password_confirmation'] ?? null,
            $data['name'] ?? $data['nama'] ?? null,
            $data['phone_number'] ?? $data['no_hp'] ?? null,
            $data['email'] ?? null,
            isset($data['prodi_id']) ? (int)$data['prodi_id'] : null,
            isset($data['kelas_id']) ? (int)$data['kelas_id'] : null,
            $userType ?: ($data['user_type'] ?? $data['level_kode'] ?? ''),
            $partialUpdate
        );
    }
    
    /**
     * Create UpdateUserDTO for Admin
     * 
     * @param array $data
     * @param bool $partialUpdate
     * @return self
     */
    public static function forAdmin(array $data, bool $partialUpdate = true): self
    {
        return self::fromArray($data, 'ADM', $partialUpdate);
    }
    
    /**
     * Create UpdateUserDTO for Dosen
     * 
     * @param array $data
     * @param bool $partialUpdate
     * @return self
     */
    public static function forDosen(array $data, bool $partialUpdate = true): self
    {
        return self::fromArray($data, 'DSN', $partialUpdate);
    }
    
    /**
     * Create UpdateUserDTO for Tendik
     * 
     * @param array $data
     * @param bool $partialUpdate
     * @return self
     */
    public static function forTendik(array $data, bool $partialUpdate = true): self
    {
        return self::fromArray($data, 'TDK', $partialUpdate);
    }
    
    /**
     * Create UpdateUserDTO for Mahasiswa
     * 
     * @param array $data
     * @param bool $partialUpdate
     * @return self
     */
    public static function forMahasiswa(array $data, bool $partialUpdate = true): self
    {
        return self::fromArray($data, 'MHS', $partialUpdate);
    }
    
    /**
     * Sanitize input data to prevent SQL injection and XSS attacks
     * 
     * Validates: Requirements 11.4, 19.4, 30.1
     * 
     * @param string|null $input
     * @return string|null
     */
    private function sanitize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        
        // Remove potential SQL injection patterns
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);
        $input = str_replace([';', '\'', '"', '`', '--', '/*', '*/'], '', $input);
        
        // Prevent XSS by escaping HTML special characters
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Trim whitespace
        $input = trim($input);
        
        return $input;
    }
    
    /**
     * Validate DTO data
     * 
     * Validates: Requirements 11.1, 11.2, 11.3, 19.1, 19.2, 19.3
     * 
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $errors = [];
        
        // Sanitize all inputs before validation
        $this->username = $this->sanitize($this->username);
        $this->currentPassword = $this->sanitize($this->currentPassword);
        $this->newPassword = $this->sanitize($this->newPassword);
        $this->passwordConfirmation = $this->sanitize($this->passwordConfirmation);
        $this->name = $this->sanitize($this->name);
        $this->phoneNumber = $this->sanitize($this->phoneNumber);
        $this->email = $this->sanitize($this->email);
        $this->userType = $this->sanitize($this->userType);
        
        // Username validation (if provided)
        if ($this->username !== null) {
            if (empty($this->username)) {
                $errors['username'] = ['Username tidak boleh kosong'];
            } elseif (strlen($this->username) < 3) {
                $errors['username'] = ['Username minimal 3 karakter'];
            } elseif (strlen($this->username) > 50) {
                $errors['username'] = ['Username maksimal 50 karakter'];
            } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $this->username)) {
                $errors['username'] = ['Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung'];
            }
        }
        
        // Password change validation
        $isChangingPassword = $this->newPassword !== null || $this->passwordConfirmation !== null;
        
        if ($isChangingPassword) {
            // Current password is required when changing password
            if (empty($this->currentPassword)) {
                $errors['current_password'] = ['Password saat ini wajib diisi untuk mengubah password'];
            }
            
            // New password validation
            if (empty($this->newPassword)) {
                $errors['new_password'] = ['Password baru wajib diisi'];
            } elseif (strlen($this->newPassword) < 8) {
                $errors['new_password'] = ['Password baru minimal 8 karakter'];
            } elseif (strlen($this->newPassword) > 255) {
                $errors['new_password'] = ['Password baru terlalu panjang'];
            }
            
            // Password confirmation
            if (empty($this->passwordConfirmation)) {
                $errors['password_confirmation'] = ['Konfirmasi password wajib diisi'];
            } elseif ($this->newPassword !== $this->passwordConfirmation) {
                $errors['password_confirmation'] = ['Konfirmasi password tidak cocok dengan password baru'];
            }
        }
        
        // Name validation (if provided and partial update is false)
        if (!$this->partialUpdate || $this->name !== null) {
            if (empty($this->name)) {
                $errors['name'] = ['Nama wajib diisi'];
            } elseif (strlen($this->name) < 2) {
                $errors['name'] = ['Nama minimal 2 karakter'];
            } elseif (strlen($this->name) > 100) {
                $errors['name'] = ['Nama maksimal 100 karakter'];
            }
        }
        
        // Phone number validation (if provided)
        if ($this->phoneNumber !== null) {
            if (!empty($this->phoneNumber) && !preg_match('/^[0-9]{10,15}$/', $this->phoneNumber)) {
                $errors['phone_number'] = ['Nomor telepon harus berupa angka 10-15 digit'];
            }
        }
        
        // Email validation (if provided)
        if ($this->email !== null) {
            if (!empty($this->email)) {
                if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = ['Format email tidak valid'];
                } elseif (strlen($this->email) > 100) {
                    $errors['email'] = ['Email maksimal 100 karakter'];
                }
            }
        }
        
        // Prodi ID validation (if provided and required for user type)
        if ($this->prodiId !== null && in_array($this->userType, ['ADM', 'DSN', 'MHS'])) {
            if (!is_numeric($this->prodiId) || $this->prodiId <= 0) {
                $errors['prodi_id'] = ['ID program studi tidak valid'];
            }
        }
        
        // Kelas ID validation (if provided and user type is Mahasiswa)
        if ($this->kelasId !== null && $this->userType === 'MHS') {
            if (!is_numeric($this->kelasId) || $this->kelasId <= 0) {
                $errors['kelas_id'] = ['ID kelas tidak valid'];
            }
        }
        
        // Security validation - check for SQL injection patterns
        $sqlInjectionPatterns = [
            '/SELECT.*FROM/i',
            '/INSERT.*INTO/i',
            '/UPDATE.*SET/i',
            '/DELETE.*FROM/i',
            '/DROP.*TABLE/i',
            '/UNION.*SELECT/i',
            '/OR.*=.*OR/i',
            '/--.*$/',
            '/\/\*.*\*\//',
        ];
        
        $fields = [
            'username' => $this->username,
            'currentPassword' => $this->currentPassword,
            'newPassword' => $this->newPassword,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'email' => $this->email,
        ];
        
        foreach ($fields as $fieldName => $fieldValue) {
            if ($fieldValue !== null) {
                foreach ($sqlInjectionPatterns as $pattern) {
                    if (preg_match($pattern, $fieldValue)) {
                        $errors['security'] = ['Input mengandung pola SQL injection yang tidak diperbolehkan'];
                        break 2;
                    }
                }
            }
        }
        
        // Security validation - check for XSS patterns
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            '/onmouseover=/i',
            '/alert\(/i',
            '/document\./i',
            '/window\./i',
        ];
        
        foreach ($fields as $fieldName => $fieldValue) {
            if ($fieldValue !== null) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $fieldValue)) {
                        $errors['security'] = ['Input mengandung pola XSS yang tidak diperbolehkan'];
                        break 2;
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            return ValidationResult::failure('Validasi data pembaruan pengguna gagal', $errors);
        }
        
        return ValidationResult::success();
    }
    
    /**
     * Check if password is being changed
     * 
     * @return bool
     */
    public function isChangingPassword(): bool
    {
        return $this->newPassword !== null && $this->passwordConfirmation !== null;
    }
    
    /**
     * Check if username is being updated
     * 
     * @return bool
     */
    public function isUpdatingUsername(): bool
    {
        return $this->username !== null;
    }
    
    /**
     * Convert DTO to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'current_password' => $this->currentPassword,
            'new_password' => $this->newPassword,
            'password_confirmation' => $this->passwordConfirmation,
            'name' => $this->name,
            'phone_number' => $this->phoneNumber,
            'email' => $this->email,
            'prodi_id' => $this->prodiId,
            'kelas_id' => $this->kelasId,
            'user_type' => $this->userType,
            'partial_update' => $this->partialUpdate,
        ];
    }
    
    /**
     * Get only the fields that are being updated
     * 
     * @return array
     */
    public function getUpdatedFields(): array
    {
        $fields = [];
        
        if ($this->username !== null) {
            $fields['username'] = $this->username;
        }
        
        if ($this->name !== null) {
            $fields['name'] = $this->name;
        }
        
        if ($this->phoneNumber !== null) {
            $fields['phone_number'] = $this->phoneNumber;
        }
        
        if ($this->email !== null) {
            $fields['email'] = $this->email;
        }
        
        if ($this->prodiId !== null) {
            $fields['prodi_id'] = $this->prodiId;
        }
        
        if ($this->kelasId !== null) {
            $fields['kelas_id'] = $this->kelasId;
        }
        
        return $fields;
    }
    
    /**
     * Get password-related fields
     * 
     * @return array
     */
    public function getPasswordFields(): array
    {
        return [
            'current_password' => $this->currentPassword,
            'new_password' => $this->newPassword,
        ];
    }
}