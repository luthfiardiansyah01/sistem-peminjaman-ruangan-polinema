<?php

namespace App\DTO;

/**
 * Validation Result Data Transfer Object
 * 
 * This DTO encapsulates validation results for structured error handling
 * Used by DTO classes to return validation results in a consistent format
 * 
 * Validates: Requirements 11.2, 11.3, 19.2, 19.3
 * 
 * @package App\DTO
 */
class ValidationResult
{
    /**
     * @var bool Whether validation passed
     */
    public bool $isValid;
    
    /**
     * @var string|null Error message if validation failed
     */
    public ?string $errorMessage;
    
    /**
     * @var array Validation error details (field => [messages])
     */
    public array $validationErrors;
    
    /**
     * Constructor
     * 
     * @param bool $isValid Whether validation passed
     * @param string|null $errorMessage Error message if validation failed
     * @param array $validationErrors Validation error details
     */
    public function __construct(bool $isValid = true, ?string $errorMessage = null, array $validationErrors = [])
    {
        $this->isValid = $isValid;
        $this->errorMessage = $errorMessage;
        $this->validationErrors = $validationErrors;
    }
    
    /**
     * Create a successful validation result
     * 
     * @return self
     */
    public static function success(): self
    {
        return new self(true);
    }
    
    /**
     * Create a failed validation result with error message
     * 
     * @param string $errorMessage Error message
     * @param array $validationErrors Validation error details
     * @return self
     */
    public static function failure(string $errorMessage, array $validationErrors = []): self
    {
        return new self(false, $errorMessage, $validationErrors);
    }
    
    /**
     * Convert validation result to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isValid,
            'message' => $this->errorMessage,
            'errors' => $this->validationErrors,
        ];
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }
    
    /**
     * Get error message
     * 
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}