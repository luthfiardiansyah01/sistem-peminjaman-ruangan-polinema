<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Standardized error response system
 * 
 * This class provides standardized error responses for different error categories:
 * - Validation Errors (422 Unprocessable Entity)
 * - Business Logic Errors (400 Bad Request)
 * - Database Errors (500 Internal Server Error)
 * - Authorization Errors (403 Forbidden)
 * 
 * Validates: Requirements 10.1, 10.2, 10.3, 10.4, 18.1, 18.2, 18.3, 18.4
 */
class ErrorResponse
{
    /**
     * Error code constants
     */
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const BUSINESS_LOGIC_ERROR = 'BUSINESS_LOGIC_ERROR';
    public const DATABASE_ERROR = 'DATABASE_ERROR';
    public const AUTHORIZATION_ERROR = 'UNAUTHORIZED';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const NOT_FOUND_ERROR = 'NOT_FOUND_ERROR';

    /**
     * Create a standardized error response
     *
     * @param string $message Error message
     * @param string $errorCode Error code (use class constants)
     * @param array $details Additional error details (e.g., validation errors)
     * @param int $httpStatusCode HTTP status code
     * @return JsonResponse
     */
    public static function create(string $message, string $errorCode, array $details = [], int $httpStatusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            // 'status' duplikat dari 'success' — sebagian view lama (mis. mahasiswa/edit_ajax.blade.php)
            // mengecek response.status, bukan response.success, dan sempat menampilkan "Gagal!" palsu
            // walau request sukses karena key ini tidak pernah ada di response.
            'status' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, $httpStatusCode);
    }

    /**
     * Create validation error response (422 Unprocessable Entity)
     *
     * Validates: Requirement 10.1, 18.2
     * 
     * @param string $message Error message (defaults to validation error)
     * @param array $validationErrors Validation error details (field => [messages])
     * @return JsonResponse
     */
    public static function validationError(string $message = 'Validasi gagal', array $validationErrors = []): JsonResponse
    {
        // Format validation errors for structured response
        $details = [];
        if (!empty($validationErrors)) {
            $details = ['validation_errors' => $validationErrors];
        }

        return self::create($message, self::VALIDATION_ERROR, $details, 422);
    }

    /**
     * Create business logic error response (400 Bad Request)
     *
     * Validates: Requirement 10.2, 18.3
     * 
     * @param string $message Domain-specific error message
     * @param array $details Additional error details
     * @return JsonResponse
     */
    public static function businessLogicError(string $message, array $details = []): JsonResponse
    {
        return self::create($message, self::BUSINESS_LOGIC_ERROR, $details, 400);
    }

    /**
     * Create database error response (500 Internal Server Error)
     *
     * Validates: Requirement 10.2, 10.3
     * 
     * @param \Throwable $exception The database exception
     * @param string|null $customMessage Optional custom message (defaults to generic database error)
     * @param bool $shouldLog Whether to log the error (defaults to true)
     * @return JsonResponse
     */
    public static function databaseError(\Throwable $exception, ?string $customMessage = null, bool $shouldLog = true): JsonResponse
    {
        $message = $customMessage ?? 'Terjadi kesalahan pada database. Silakan coba lagi nanti.';
        
        if ($shouldLog) {
            Log::error('Database error occurred', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        // For security, don't expose database details in production
        $details = config('app.debug') ? [
            'debug_message' => $exception->getMessage(),
            'debug_code' => $exception->getCode(),
        ] : [];

        return self::create($message, self::DATABASE_ERROR, $details, 500);
    }

    /**
     * Create authorization error response (403 Forbidden)
     *
     * Validates: Requirement 10.4, 18.4
     * 
     * @param string $message Error message (defaults to access denied)
     * @param string|null $redirectUrl URL to redirect to (for web views)
     * @return JsonResponse
     */
    public static function authorizationError(string $message = 'Akses ditolak', ?string $redirectUrl = null): JsonResponse
    {
        $details = [];
        if ($redirectUrl) {
            $details['redirect_url'] = $redirectUrl;
        }

        return self::create($message, self::AUTHORIZATION_ERROR, $details, 403);
    }

    /**
     * Create not found error response (404 Not Found)
     *
     * @param string $message Error message (defaults to resource not found)
     * @param string|null $resourceType Type of resource not found
     * @param string|null $resourceId Resource identifier
     * @return JsonResponse
     */
    public static function notFoundError(string $message = 'Resource tidak ditemukan', ?string $resourceType = null, ?string $resourceId = null): JsonResponse
    {
        $details = [];
        if ($resourceType) {
            $details['resource_type'] = $resourceType;
        }
        if ($resourceId) {
            $details['resource_id'] = $resourceId;
        }

        return self::create($message, self::NOT_FOUND_ERROR, $details, 404);
    }

    /**
     * Create internal server error response (500 Internal Server Error)
     *
     * @param \Throwable $exception The exception
     * @param string|null $customMessage Optional custom message
     * @param bool $shouldLog Whether to log the error (defaults to true)
     * @return JsonResponse
     */
    public static function internalError(\Throwable $exception, ?string $customMessage = null, bool $shouldLog = true): JsonResponse
    {
        $message = $customMessage ?? 'Terjadi kesalahan internal pada server. Silakan coba lagi nanti.';
        
        if ($shouldLog) {
            Log::error('Internal server error occurred', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        // For security, don't expose internal details in production
        $details = config('app.debug') ? [
            'debug_message' => $exception->getMessage(),
            'debug_type' => get_class($exception),
        ] : [];

        return self::create($message, self::INTERNAL_ERROR, $details, 500);
    }

    /**
     * Convert validation exception to standardized error response
     *
     * @param \Illuminate\Validation\ValidationException $exception
     * @return JsonResponse
     */
    public static function fromValidationException(\Illuminate\Validation\ValidationException $exception): JsonResponse
    {
        return self::validationError(
            'Validasi gagal',
            $exception->errors()
        );
    }

    /**
     * Convert authorization exception to standardized error response
     *
     * @param \Illuminate\Auth\Access\AuthorizationException $exception
     * @param string|null $redirectUrl URL to redirect to
     * @return JsonResponse
     */
    public static function fromAuthorizationException(\Illuminate\Auth\Access\AuthorizationException $exception, ?string $redirectUrl = null): JsonResponse
    {
        $message = $exception->getMessage() ?: 'Akses ditolak';
        return self::authorizationError($message, $redirectUrl);
    }

    /**
     * Convert model not found exception to standardized error response
     *
     * @param \Illuminate\Database\Eloquent\ModelNotFoundException $exception
     * @return JsonResponse
     */
    public static function fromModelNotFoundException(\Illuminate\Database\Eloquent\ModelNotFoundException $exception): JsonResponse
    {
        $model = class_basename($exception->getModel());
        $message = "Data {$model} tidak ditemukan";
        
        return self::notFoundError($message, $model);
    }

    /**
     * Create success response (for consistency)
     *
     * @param string $message Success message
     * @param array $data Additional data
     * @param int $httpStatusCode HTTP status code (defaults to 200)
     * @return JsonResponse
     */
    public static function success(string $message = 'Operasi berhasil', array $data = [], int $httpStatusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            // lihat catatan di create() — 'status' duplikat dari 'success' untuk kompatibilitas
            // dengan view lama yang mengecek response.status.
            'status' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $httpStatusCode);
    }

    /**
     * Helper method to create response from service layer result array
     *
     * @param array $result Service layer result array
     * @param int $successStatusCode HTTP status code for success (defaults to 200)
     * @return JsonResponse
     */
    public static function fromServiceResult(array $result, int $successStatusCode = 200): JsonResponse
    {
        if ($result['success'] ?? false) {
            $data = $result;
            unset($data['success'], $data['message']);
            
            // If only success and message remain, use empty data
            if (empty($data)) {
                $data = [];
            }
            
            return self::success($result['message'] ?? 'Operasi berhasil', $data, $successStatusCode);
        }

        // Determine error type based on result structure
        $message = $result['message'] ?? 'Terjadi kesalahan';
        $errors = $result['errors'] ?? [];
        
        // Check if it's a validation error (has validation_errors or similar pattern)
        if (!empty($errors) && (isset($errors['validation_errors']) || is_array($errors))) {
            return self::validationError($message, $errors);
        }
        
        // Default to business logic error
        return self::businessLogicError($message, $errors);
    }
}