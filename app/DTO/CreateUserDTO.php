<?php

namespace App\DTO;

/**
 * Data Transfer Object for creating users
 * 
 * This DTO encapsulates user creation data for all user types (Admin, Dosen, Tendik, Mahasiswa)
 * with comprehensive validation and sanitization to prevent SQL injection and XSS attacks
 * 
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4, 19.1, 19.2, 19.3, 19.4, 30.1
 * 
 * @package App\DTO
 */
class CreateUserDTO
{
    /**
     * @var string Username
     */
    public string $username;
    
    /**
     * @var string Password
     */
    public string $password;
    
    /**
     * @var string User type (ADM, DSN, TDK, MHS)
     */
    public string $userType;
    
    /**
     * @var string|null Name (nama)
     */
    public ?string $name;
    
    /**
     * @var string|null Identifier (NIDN for Admin/Dosen/Tendik, NIM for Mahasiswa)
     */
    public ?string $identifier;
    
    /**
     * @var string|null Phone number
     */
    public ?string $phoneNumber;
    
    /**
     * @var int|null Program study ID
     */
    public ?int $prodiId;
    
    /**
     * @var int|null Class ID (for Mahasiswa only)
     */
    public ?int $kelasId;
    
    /**
     * @var string|null Email
     */
    public ?string $email;
    
    /**
     * Constructor
     * 
     * @param string $username
     * @param string $password
     * @param string $userType
     * @param string|null $name
     * @param string|null $identifier
     * @param string|null $phoneNumber
     * @param int|null $prodiId
     * @param int|null $kelasId
     * @param string|null $email
     */
    public function __construct(
        string $username,
        string $password,
        string $userType,
        ?string $name = null,
        ?string $identifier = null,
        ?string $phoneNumber = null,
        ?int $prodiId = null,
        ?int $kelasId = null,
        ?string $email = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->userType = $userType;
        $this->name = $name;
        $this->identifier = $identifier;
        $this->phoneNumber = $phoneNumber;
        $this->prodiId = $prodiId;
        $this->kelasId = $kelasId;
        $this->email = $email;
    }
    
    /**
     * Create CreateUserDTO from array
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['username'] ?? '',
            $data['password'] ?? '',
            $data['user_type'] ?? $data['level_kode'] ?? '',
            $data['name'] ?? $data['nama'] ?? null,
            $data['identifier'] ?? $data['nidn'] ?? $data['nim'] ?? null,
            $data['phone_number'] ?? $data['no_hp'] ?? null,
            isset($data['prodi_id']) ? (int)$data['prodi_id'] : null,
            isset($data['kelas_id']) ? (int)$data['kelas_id'] : null,
            $data['email'] ?? null
        );
    }
    
    /**
     * Create CreateUserDTO for Admin
     * 
     * @param array $data
     * @return self
     */
    public static function forAdmin(array $data): self
    {
        $data['user_type'] = 'ADM';
        return self::fromArray($data);
    }
    
    /**
     * Create CreateUserDTO for Dosen
     * 
     * @param array $data
     * @return self
     */
    public static function forDosen(array $data): self
    {
        $data['user_type'] = 'DSN';
        return self::fromArray($data);
    }
    
    /**
     * Create CreateUserDTO for Tendik
     * 
     * @param array $data
     * @return self
     */
    public static function forTendik(array $data): self
    {
        $data['user_type'] = 'TDK';
        return self::fromArray($data);
    }
    
    /**
     * Create CreateUserDTO for Mahasiswa
     * 
     * @param array $data
     * @return self
     */
    public static function forMahasiswa(array $data): self
    {
        $data['user_type'] = 'MHS';
        return self::fromArray($data);
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
        $this->password = $this->sanitize($this->password);
        $this->userType = $this->sanitize($this->userType);
        $this->name = $this->sanitize($this->name);
        $this->identifier = $this->sanitize($this->identifier);
        $this->phoneNumber = $this->sanitize($this->phoneNumber);
        $this->email = $this->sanitize($this->email);
        
        // Common validations
        if (empty($this->username)) {
            $errors['username'] = ['Username wajib diisi'];
        } elseif (strlen($this->username) < 3) {
            $errors['username'] = ['Username minimal 3 karakter'];
        } elseif (strlen($this->username) > 50) {
            $errors['username'] = ['Username maksimal 50 karakter'];
        } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $this->username)) {
            $errors['username'] = ['Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung'];
        }
        
        if (empty($this->password)) {
            $errors['password'] = ['Password wajib diisi'];
        } elseif (strlen($this->password) < 8) {
            $errors['password'] = ['Password minimal 8 karakter'];
        } elseif (strlen($this->password) > 255) {
            $errors['password'] = ['Password terlalu panjang'];
        }
        
        if (empty($this->userType)) {
            $errors['user_type'] = ['Tipe pengguna wajib diisi'];
        } elseif (!in_array($this->userType, ['ADM', 'DSN', 'TDK', 'MHS'])) {
            $errors['user_type'] = ['Tipe pengguna tidak valid'];
        }
        
        // Name validation
        if (empty($this->name)) {
            $errors['name'] = ['Nama wajib diisi'];
        } elseif (strlen($this->name) < 2) {
            $errors['name'] = ['Nama minimal 2 karakter'];
        } elseif (strlen($this->name) > 100) {
            $errors['name'] = ['Nama maksimal 100 karakter'];
        }
        
        // Identifier validation based on user type
        if (empty($this->identifier)) {
            $errors['identifier'] = ['Identifikasi wajib diisi'];
        } else {
            switch ($this->userType) {
                case 'ADM':
                case 'DSN':
                case 'TDK':
                    // NIDN validation (typically 10-16 digits)
                    if (!preg_match('/^[0-9]{10,16}$/', $this->identifier)) {
                        $errors['identifier'] = ['NIDN harus berupa angka 10-16 digit'];
                    }
                    break;
                    
                case 'MHS':
                    // NIM validation (alphanumeric, typically 8-15 characters)
                    if (!preg_match('/^[A-Za-z0-9]{8,15}$/', $this->identifier)) {
                        $errors['identifier'] = ['NIM harus berupa alfanumerik 8-15 karakter'];
                    }
                    break;
            }
        }
        
        // Phone number validation
        if (!empty($this->phoneNumber)) {
            if (!preg_match('/^[0-9]{10,15}$/', $this->phoneNumber)) {
                $errors['phone_number'] = ['Nomor telepon harus berupa angka 10-15 digit'];
            }
        }
        
        // Email validation (optional)
        if (!empty($this->email)) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['Format email tidak valid'];
            } elseif (strlen($this->email) > 100) {
                $errors['email'] = ['Email maksimal 100 karakter'];
            }
        }
        
        // Prodi ID validation (required for certain user types)
        if (in_array($this->userType, ['ADM', 'DSN', 'MHS'])) {
            if (empty($this->prodiId)) {
                $errors['prodi_id'] = ['Program studi wajib diisi'];
            } elseif (!is_numeric($this->prodiId) || $this->prodiId <= 0) {
                $errors['prodi_id'] = ['ID program studi tidak valid'];
            }
        }
        
        // Kelas ID validation (required for Mahasiswa only)
        if ($this->userType === 'MHS') {
            if (empty($this->kelasId)) {
                $errors['kelas_id'] = ['Kelas wajib diisi'];
            } elseif (!is_numeric($this->kelasId) || $this->kelasId <= 0) {
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
            'name' => $this->name,
            'identifier' => $this->identifier,
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
            return ValidationResult::failure('Validasi data pengguna gagal', $errors);
        }
        
        return ValidationResult::success();
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
            'password' => $this->password,
            'user_type' => $this->userType,
            'name' => $this->name,
            'identifier' => $this->identifier,
            'phone_number' => $this->phoneNumber,
            'prodi_id' => $this->prodiId,
            'kelas_id' => $this->kelasId,
            'email' => $this->email,
        ];
    }
    
    /**
     * Get user data for UserModel
     * 
     * @return array
     */
    public function getUserData(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
    
    /**
     * Get person-specific data based on user type
     * 
     * @return array
     */
    public function getPersonData(): array
    {
        $data = [
            'nama' => $this->name,
            'no_hp' => $this->phoneNumber,
        ];
        
        switch ($this->userType) {
            case 'ADM':
                $data['admin_nidn'] = $this->identifier;
                $data['admin_nama'] = $this->name;
                $data['admin_noHp'] = $this->phoneNumber;
                $data['prodi_id'] = $this->prodiId;
                break;
                
            case 'DSN':
                $data['dosen_nidn'] = $this->identifier;
                $data['dosen_nama'] = $this->name;
                $data['dosen_noHp'] = $this->phoneNumber;
                $data['prodi_id'] = $this->prodiId;
                break;
                
            case 'TDK':
                $data['tendik_nidn'] = $this->identifier;
                $data['tendik_nama'] = $this->name;
                $data['tendik_noHp'] = $this->phoneNumber;
                break;
                
            case 'MHS':
                $data['mahasiswa_nim'] = $this->identifier;
                $data['mahasiswa_nama'] = $this->name;
                $data['mahasiswa_noHp'] = $this->phoneNumber;
                $data['prodi_id'] = $this->prodiId;
                $data['kelas_id'] = $this->kelasId;
                break;
        }
        
        return $data;
    }
    
    /**
     * Get the role code for this user type
     * 
     * @return string
     */
    public function getRoleCode(): string
    {
        return $this->userType;
    }
}