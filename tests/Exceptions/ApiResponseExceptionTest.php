<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Exceptions;

use TheWildFields\Tavriia\DTO\ApiResponseDTO;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiResponseExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $response = new ApiResponseDTO(404, 'Not Found');
        $e        = ApiResponseException::forResponse($response);

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_for_response_returns_api_response_exception(): void
    {
        $response = new ApiResponseDTO(500, 'Internal Server Error');
        $e        = ApiResponseException::forResponse($response);

        $this->assertInstanceOf(ApiResponseException::class, $e);
    }

    public function test_for_response_message_contains_status_code(): void
    {
        $response = new ApiResponseDTO(422, 'Unprocessable Entity');
        $e        = ApiResponseException::forResponse($response);

        $this->assertStringContainsString('422', $e->getMessage());
    }

    public function test_for_response_message_contains_body(): void
    {
        $response = new ApiResponseDTO(503, 'Service Unavailable');
        $e        = ApiResponseException::forResponse($response);

        $this->assertStringContainsString('Service Unavailable', $e->getMessage());
    }

    public function test_for_response_message_format(): void
    {
        $response = new ApiResponseDTO(404, 'Not Found');
        $e        = ApiResponseException::forResponse($response);

        $this->assertSame('HTTP response error: status 404 — Not Found', $e->getMessage());
    }

    public function test_get_response_returns_the_dto(): void
    {
        $response = new ApiResponseDTO(400, 'Bad Request', ['X-Custom' => 'header']);
        $e        = ApiResponseException::forResponse($response);

        $returned = $e->getResponse();

        $this->assertSame($response, $returned);
        $this->assertSame(400, $returned->statusCode);
        $this->assertSame('Bad Request', $returned->body);
    }

    public function test_exception_code_equals_status_code(): void
    {
        $response = new ApiResponseDTO(503, 'Service Unavailable');
        $e        = ApiResponseException::forResponse($response);

        $this->assertSame(503, $e->getCode());
    }

    public function test_direct_construction_stores_response(): void
    {
        $response = new ApiResponseDTO(401, 'Unauthorized');
        $e        = new ApiResponseException('Auth failed', $response);

        $this->assertSame('Auth failed', $e->getMessage());
        $this->assertSame($response, $e->getResponse());
        $this->assertSame(401, $e->getCode());
    }
}
