<?php

namespace Tests\Unit;

use App\Http\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\JsonResponse;
use Tests\CreatesApplication;

/**
 * Test for ErrorResponse class
 * 
 * Validates: Requirements 10.1, 10.2, 10.3, 10.4, 18.1, 18.2, 18.3, 18.4
 */
class ErrorResponseTest extends TestCase
{
    use CreatesApplication;

    /** @test */
    public function it_creates_validation_error_response_with_correct_structure_and_status()
    {
        $validationErrors = ['email' => ['Email tidak valid']];
        
        $response = ErrorResponse::validationError('Validasi gagal', $validationErrors);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validasi gagal', $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
        $this->assertArrayHasKey('details', $data);
        $this->assertArrayHasKey('validation_errors', $data['details']);
        $this->assertEquals($validationErrors, $data['details']['validation_errors']);
    }

    /** @test */
    public function it_creates_business_logic_error_response_with_correct_structure_and_status()
    {
        $response = ErrorResponse::businessLogicError('Username sudah terdaftar');
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Username sudah terdaftar', $data['message']);
        $this->assertEquals('BUSINESS_LOGIC_ERROR', $data['error_code']);
    }

    /** @test */
    public function it_creates_authorization_error_response_with_correct_structure_and_status()
    {
        $response = ErrorResponse::authorizationError('Akses ditolak', '/login');
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Akses ditolak', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
        $this->assertArrayHasKey('details', $data);
        $this->assertEquals('/login', $data['details']['redirect_url']);
    }

    /** @test */
    public function it_creates_not_found_error_response_with_correct_structure_and_status()
    {
        $response = ErrorResponse::notFoundError('User tidak ditemukan', 'user', '123');
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('User tidak ditemukan', $data['message']);
        $this->assertEquals('NOT_FOUND_ERROR', $data['error_code']);
        $this->assertArrayHasKey('details', $data);
        $this->assertEquals('user', $data['details']['resource_type']);
        $this->assertEquals('123', $data['details']['resource_id']);
    }

    /** @test */
    public function it_creates_success_response_with_correct_structure_and_status()
    {
        $response = ErrorResponse::success('User berhasil dibuat', ['user_id' => 123]);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('User berhasil dibuat', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals(123, $data['data']['user_id']);
    }

    /** @test */
    public function it_handles_service_result_with_success()
    {
        $serviceResult = [
            'success' => true,
            'message' => 'User created',
            'user_id' => 123,
            'additional' => 'data'
        ];
        
        $response = ErrorResponse::fromServiceResult($serviceResult, 201);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('User created', $data['message']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals(123, $data['data']['user_id']);
        $this->assertEquals('data', $data['data']['additional']);
    }

    /** @test */
    public function it_handles_service_result_with_validation_error()
    {
        $serviceResult = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => ['email' => 'Invalid email']
        ];
        
        $response = ErrorResponse::fromServiceResult($serviceResult);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
    }

    /** @test */
    public function it_handles_service_result_with_business_logic_error()
    {
        $serviceResult = [
            'success' => false,
            'message' => 'Username already exists',
        ];
        
        $response = ErrorResponse::fromServiceResult($serviceResult);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Username already exists', $data['message']);
        $this->assertEquals('BUSINESS_LOGIC_ERROR', $data['error_code']);
    }

    /** @test */
    public function it_creates_custom_error_response_with_details()
    {
        $response = ErrorResponse::create(
            'Custom error message',
            'CUSTOM_ERROR_CODE',
            ['field' => 'value', 'count' => 5],
            409
        );
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(409, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Custom error message', $data['message']);
        $this->assertEquals('CUSTOM_ERROR_CODE', $data['error_code']);
        $this->assertArrayHasKey('details', $data);
        $this->assertEquals('value', $data['details']['field']);
        $this->assertEquals(5, $data['details']['count']);
    }
}