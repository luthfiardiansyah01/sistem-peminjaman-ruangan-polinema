<?php

namespace App\DTO;

use Illuminate\Support\Str;

/**
 * Data Transfer Object for authentication requests
 * 
 * This DTO encapsulates authentication credentials with validation and sanitization
 * to prevent SQL injection and XSS attacks
 * 
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4, 19.1, 19.2, 19.3, 19.4, 30.1
 * 
 * @package App\DTO
 */
class AuthRequestDTO
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
     * @var bool|null Remember me flag
     */
    public ?bool $remember;
    
    /**
     * Constructor
     * 
     * @param string $username
     * @param string $password
     * @param bool|null $remember
     */
    public function __construct(string $username, string $password, ?bool $remember = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->remember = $remember;
    }
    
    /**
     * Create AuthRequestDTO from array
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['username'] ?? '',
            $data['password'] ?? '',
            $data['remember'] ?? false
        );
    }
    
    /**
     * Sanitize input data to prevent SQL injection and XSS attacks
     * 
     * Validates: Requirements 11.4, 19.4, 30.1
     * 
     * @param string $input
     * @return string
     */
    private function sanitize(string $input): string
    {
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
        
        // Sanitize inputs before validation
        $this->username = $this->sanitize($this->username);
        $this->password = $this->sanitize($this->password);
        
        // Validate username
        if (empty($this->username)) {
            $errors['username'] = ['Username wajib diisi'];
        } elseif (strlen($this->username) < 3) {
            $errors['username'] = ['Username minimal 3 karakter'];
        } elseif (strlen($this->username) > 50) {
            $errors['username'] = ['Username maksimal 50 karakter'];
        } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $this->username)) {
            $errors['username'] = ['Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung'];
        }
        
        // Validate password
        if (empty($this->password)) {
            $errors['password'] = ['Password wajib diisi'];
        } elseif (strlen($this->password) < 8) {
            $errors['password'] = ['Password minimal 8 karakter'];
        } elseif (strlen($this->password) > 255) {
            $errors['password'] = ['Password terlalu panjang'];
        }
        
        // Check for SQL injection patterns
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
        
        foreach ($sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $this->username) || preg_match($pattern, $this->password)) {
                $errors['security'] = ['Input mengandung pola yang tidak diperbolehkan'];
                break;
            }
        }
        
        // Check for XSS patterns
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
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $this->username) || preg_match($pattern, $this->password)) {
                $errors['security'] = ['Input mengandung pola yang tidak diperbolehkan'];
                break;
            }
        }
        
        if (!empty($errors)) {
            return ValidationResult::failure('Validasi gagal', $errors);
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
            'remember' => $this->remember,
        ];
    }
    
    /**
     * Get credentials array for Laravel Auth
     * 
     * @return array
     */
    public function getCredentials(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
    
    /**
     * Check if remember me is enabled
     * 
     * @return bool
     */
    public function shouldRemember(): bool
    {
        return $this->remember === true;
    }
}