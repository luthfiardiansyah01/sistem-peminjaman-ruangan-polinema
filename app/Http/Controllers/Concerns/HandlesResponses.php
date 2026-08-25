<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Responses\ErrorResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trait for handling standardized responses in controllers
 */
trait HandlesResponses
{
    /**
     * Create standardized JSON response
     *
     * @param array $result Service layer result array
     * @param int $successStatusCode HTTP status code for success responses (default: 200)
     * @return JsonResponse
     */
    protected function jsonResponse(array $result, int $successStatusCode = 200): JsonResponse
    {
        return ErrorResponse::fromServiceResult($result, $successStatusCode);
    }

    /**
     * Create success response
     *
     * @param string $message Success message
     * @param array $data Additional data
     * @param int $statusCode HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function successResponse(string $message = 'Operasi berhasil', array $data = [], int $statusCode = 200): JsonResponse
    {
        return ErrorResponse::success($message, $data, $statusCode);
    }

    /**
     * Create error response
     *
     * @param string $message Error message
     * @param string $errorCode Error code
     * @param array $details Error details
     * @param int $statusCode HTTP status code (default: 400)
     * @return JsonResponse
     */
    protected function errorResponse(string $message, string $errorCode, array $details = [], int $statusCode = 400): JsonResponse
    {
        return ErrorResponse::create($message, $errorCode, $details, $statusCode);
    }

    /**
     * Check if request is AJAX
     *
     * @param Request $request
     * @return bool
     */
    protected function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    /**
     * Handle validation errors
     *
     * @param array $validationErrors Validation errors array
     * @param string $message Error message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $validationErrors, string $message = 'Validasi gagal'): JsonResponse
    {
        return ErrorResponse::validationError($message, $validationErrors);
    }

    /**
     * Handle business logic errors
     *
     * @param string $message Domain-specific error message
     * @param array $details Additional error details
     * @return JsonResponse
     */
    protected function businessLogicErrorResponse(string $message, array $details = []): JsonResponse
    {
        return ErrorResponse::businessLogicError($message, $details);
    }

    /**
     * Handle database errors
     *
     * @param \Throwable $exception Database exception
     * @param string|null $customMessage Optional custom message
     * @return JsonResponse
     */
    protected function databaseErrorResponse(\Throwable $exception, ?string $customMessage = null): JsonResponse
    {
        return ErrorResponse::databaseError($exception, $customMessage);
    }

    /**
     * Handle authorization errors
     *
     * @param string $message Error message
     * @param string|null $redirectUrl URL to redirect to
     * @return JsonResponse
     */
    protected function authorizationErrorResponse(string $message = 'Akses ditolak', ?string $redirectUrl = null): JsonResponse
    {
        return ErrorResponse::authorizationError($message, $redirectUrl);
    }

    /**
     * Handle not found errors
     *
     * @param string $message Error message
     * @param string|null $resourceType Type of resource not found
     * @param string|null $resourceId Resource identifier
     * @return JsonResponse
     */
    protected function notFoundErrorResponse(string $message = 'Resource tidak ditemukan', ?string $resourceType = null, ?string $resourceId = null): JsonResponse
    {
        return ErrorResponse::notFoundError($message, $resourceType, $resourceId);
    }

    /**
     * Handle DataTables error response
     *
     * @param Request $request
     * @param \Throwable $exception
     * @return JsonResponse
     */
    protected function dataTableErrorResponse(Request $request, \Throwable $exception): JsonResponse
    {
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Error: ' . $exception->getMessage(),
        ]);
    }
}