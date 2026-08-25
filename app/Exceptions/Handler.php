<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render($request, Throwable $exception): Response
    {
        // Handle AJAX/JSON requests with standardized error responses
        if ($request->ajax() || $request->wantsJson()) {
            return $this->renderJsonError($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render exception as JSON error response
     *
     * @param Throwable $exception
     * @return Response
     */
    protected function renderJsonError(Throwable $exception): Response
    {
        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            return ErrorResponse::fromValidationException($exception);
        }

        // Handle authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return ErrorResponse::authorizationError(
                'Anda harus login untuk mengakses halaman ini.',
                route('login')
            );
        }

        // Handle authorization exceptions
        if ($exception instanceof AuthorizationException) {
            return ErrorResponse::fromAuthorizationException($exception);
        }

        // Handle model not found exceptions
        if ($exception instanceof ModelNotFoundException) {
            return ErrorResponse::fromModelNotFoundException($exception);
        }

        // Handle not found HTTP exceptions
        if ($exception instanceof NotFoundHttpException) {
            return ErrorResponse::notFoundError('Endpoint tidak ditemukan');
        }

        // Handle HTTP exceptions with specific status codes
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage() ?: Response::$statusTexts[$statusCode] ?? 'Terjadi kesalahan';

            // Map status codes to appropriate error types
            if ($statusCode === 403) {
                return ErrorResponse::authorizationError($message);
            } elseif ($statusCode === 404) {
                return ErrorResponse::notFoundError($message);
            } elseif ($statusCode === 422) {
                return ErrorResponse::validationError($message);
            } elseif ($statusCode >= 400 && $statusCode < 500) {
                return ErrorResponse::businessLogicError($message);
            } elseif ($statusCode >= 500) {
                return ErrorResponse::internalError($exception);
            }
        }

        // Handle database exceptions
        if ($exception instanceof \Illuminate\Database\QueryException ||
            $exception instanceof \Illuminate\Database\ConnectionException) {
            return ErrorResponse::databaseError($exception);
        }

        // Default internal error for all other exceptions
        return ErrorResponse::internalError($exception);
    }
}
