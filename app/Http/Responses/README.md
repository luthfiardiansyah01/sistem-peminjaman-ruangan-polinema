# ErrorResponse System

## Overview

The `ErrorResponse` class provides a standardized error handling system for the SPR JTI application. It handles different error categories with appropriate HTTP status codes and structured responses.

## Features

- Standardized error responses for all error categories
- Consistent JSON response structure
- Proper HTTP status codes
- Structured error details
- Integration with Laravel exceptions
- Support for service layer results

## Response Structure

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE",
  "details": {...}
}
```

## Error Categories

| Error Type | HTTP Status | Error Code | Description |
|------------|-------------|------------|-------------|
| Validation | 422 | `VALIDATION_ERROR` | Input validation failed |
| Business Logic | 400 | `BUSINESS_LOGIC_ERROR` | Business rule violation |
| Database | 500 | `DATABASE_ERROR` | Database operation failed |
| Authorization | 403 | `UNAUTHORIZED` | Access denied |
| Not Found | 404 | `NOT_FOUND_ERROR` | Resource not found |
| Internal | 500 | `INTERNAL_ERROR` | Internal server error |

## Usage Examples

### 1. In Controllers (using HandlesResponses trait)

```php
// Use the trait in your controller
use App\Http\Controllers\Concerns\HandlesResponses;

class UserController extends Controller
{
    // Success response
    return $this->successResponse('User created', ['user_id' => 123], 201);
    
    // Error responses
    return $this->validationErrorResponse(['email' => 'Invalid email']);
    return $this->businessLogicErrorResponse('Username already exists');
    return $this->notFoundErrorResponse('User not found', 'user', '123');
    return $this->authorizationErrorResponse('Access denied', '/login');
    
    // From service result (recommended pattern)
    return $this->jsonResponse($service->createUser($data), 201);
}
```

### 2. Direct ErrorResponse Usage

```php
use App\Http\Responses\ErrorResponse;

// Validation error
return ErrorResponse::validationError(
    'Validation failed',
    ['email' => ['Email is invalid']]
);

// Business logic error
return ErrorResponse::businessLogicError('Username already exists');

// Database error
try {
    // Database operation
} catch (\Exception $e) {
    return ErrorResponse::databaseError($e);
}

// Authorization error
return ErrorResponse::authorizationError('Access denied', route('login'));

// Success response
return ErrorResponse::success('User created', ['user_id' => 123]);

// Convert service result
return ErrorResponse::fromServiceResult($service->createUser($data));
```

### 3. In Service Layer

```php
class UserService
{
    public function createUser(array $data): array
    {
        // Validation
        if (empty($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email is required',
                'errors' => ['email' => 'Email is required']
            ];
        }
        
        // Business logic check
        if ($this->userExists($data['email'])) {
            return [
                'success' => false,
                'message' => 'User already exists',
                'errors' => ['email' => 'User already exists']
            ];
        }
        
        try {
            // Database operation
            $user = User::create($data);
            
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $user->id,
                'email' => $user->email
            ];
        } catch (\Exception $e) {
            // Database error
            return [
                'success' => false,
                'message' => 'Failed to create user',
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }
}
```

### 4. Exception Handling

The Exception Handler is already configured to convert exceptions to standardized error responses:

```php
// These will automatically return appropriate error responses:
throw new ValidationException($validator);
throw new AuthorizationException('Access denied');
throw new ModelNotFoundException();
throw new NotFoundHttpException();
```

## Integration with Existing Code

The system integrates with existing controllers through:

1. **Base Controller**: All controllers now have the `HandlesResponses` trait
2. **Existing `serviceJsonResponse` method**: Updated to use ErrorResponse
3. **Exception Handler**: Converts exceptions to standardized responses

## Testing

Unit tests are available in `tests/Unit/ErrorResponseTest.php`.

Run tests with:
```bash
php artisan test --filter=ErrorResponseTest
```

## Requirements Validation

This implementation validates the following requirements:

- **Requirement 10.1**: Validation errors return 422 with structured details
- **Requirement 10.2**: Business logic errors return 400 with domain-specific messages
- **Requirement 10.3**: Database errors return 500 with logging
- **Requirement 10.4**: Authorization errors return 403 with redirect support
- **Requirement 18.1**: All error conditions return appropriate HTTP status codes
- **Requirement 18.2**: Validation errors include structured error details
- **Requirement 18.3**: Business logic errors include domain-specific messages
- **Requirement 18.4**: Authorization errors include redirect support