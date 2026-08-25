<?php

namespace App\DTO;

/**
 * Base Data Transfer Object for importing users from Excel
 * 
 * This abstract class provides common validation and sanitization for user imports
 * 
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4, 19.1, 19.2, 19.3, 19.4, 30.1
 * 
 * @package App\DTO
 */
abstract class ImportUserDTO
{
    /**
     * @var string Identifier (NIDN/NIM)
     */
    public string $identifier;
    
    /**
     * @var string Name
     */
    public string $name;
    
    /**
     * @var string|null Phone number
     */
    public ?string $phoneNumber;
    
    /**
     * @var string|null Email
     */
    public ?string $email;
    
    /**
     * @var string|null Program study name (for prodi lookup)
     */
    public ?string $prodiName;
    
    /**
     * @var string|null Class name (for Mahasiswa only)
     */
    public ?string $className;
    
    /**
     * @var string User type (ADM, DSN, TDK, MHS)
     */
    public string $userType;
    
    /**
     * Constructor
     * 
     * @param string $identifier
     * @param string $name
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param string|null $prodiName
     * @param string|null $className
     */
    public function __construct(
        string $identifier,
        string $name,
        ?string $phoneNumber = null,
        ?string $email = null,
        ?string $prodiName = null,
        ?string $className = null
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
        $this->prodiName = $prodiName;
        $this->className = $className;
        $this->userType = static::getUserType();
    }
    
    /**
     * Get the user type for this DTO
     * 
     * @return string
     */
    abstract public static function getUserType(): string;
    
    /**
     * Get identifier field name based on user type
     * 
     * @return string
     */
    abstract public static function getIdentifierFieldName(): string;
    
    /**
     * Sanitize input data to prevent SQL injection and XSS attacks
     * 
     * Validates: Requirements 11.4, 19.4, 30.1
     * 
     * @param string|null $input
     * @return string|null
     */
    protected function sanitize(?string $input): ?string
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
        $this->identifier = $this->sanitize($this->identifier);
        $this->name = $this->sanitize($this->name);
        $this->phoneNumber = $this->sanitize($this->phoneNumber);
        $this->email = $this->sanitize($this->email);
        $this->prodiName = $this->sanitize($this->prodiName);
        $this->className = $this->sanitize($this->className);
        
        // Call concrete validation method
        $this->validateConcrete($errors);
        
        // Security validation - check for SQL injection patterns
        $this->validateSecurity($errors);
        
        if (!empty($errors)) {
            return ValidationResult::failure('Validasi data import gagal', $errors);
        }
        
        return ValidationResult::success();
    }
    
    /**
     * Concrete validation to be implemented by child classes
     * 
     * @param array $errors Reference to errors array
     */
    abstract protected function validateConcrete(array &$errors): void;
    
    /**
     * Security validation for SQL injection and XSS
     * 
     * @param array $errors Reference to errors array
     */
    protected function validateSecurity(array &$errors): void
    {
        $fields = [
            'identifier' => $this->identifier,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'email' => $this->email,
            'prodiName' => $this->prodiName,
            'className' => $this->className,
        ];
        
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
    }
    
    /**
     * Convert DTO to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'name' => $this->name,
            'phone_number' => $this->phoneNumber,
            'email' => $this->email,
            'prodi_name' => $this->prodiName,
            'class_name' => $this->className,
            'user_type' => $this->userType,
        ];
    }
    
    /**
     * Convert to CreateUserDTO
     * 
     * @param int|null $prodiId
     * @param int|null $kelasId
     * @return CreateUserDTO
     */
    public function toCreateUserDTO(?int $prodiId = null, ?int $kelasId = null): CreateUserDTO
    {
        // Generate username from identifier (lowercase, alphanumeric only)
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->identifier));
        
        // Generate default password (identifier + '123')
        $password = $this->identifier . '123';
        
        return new CreateUserDTO(
            $username,
            $password,
            $this->userType,
            $this->name,
            $this->identifier,
            $this->phoneNumber,
            $prodiId,
            $kelasId,
            $this->email
        );
    }
}