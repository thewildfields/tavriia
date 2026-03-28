<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use TheWildFields\Tavriia\DTO\ApiResponseDTO;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiResponseDTOTest extends TestCase
{
    public function test_construction_with_all_params(): void
    {
        $dto = new ApiResponseDTO(
            statusCode: 200,
            body: '{"ok":true}',
            headers: ['Content-Type' => 'application/json'],
        );

        $this->assertSame(200, $dto->statusCode);
        $this->assertSame('{"ok":true}', $dto->body);
        $this->assertSame(['Content-Type' => 'application/json'], $dto->headers);
    }

    public function test_headers_default_to_empty_array(): void
    {
        $dto = new ApiResponseDTO(statusCode: 200, body: '');

        $this->assertSame([], $dto->headers);
    }

    // --- isSuccess() ---

    public function test_is_success_true_for_200(): void
    {
        $dto = new ApiResponseDTO(200, '');
        $this->assertTrue($dto->isSuccess());
    }

    public function test_is_success_true_for_201(): void
    {
        $dto = new ApiResponseDTO(201, '');
        $this->assertTrue($dto->isSuccess());
    }

    public function test_is_success_true_for_299(): void
    {
        $dto = new ApiResponseDTO(299, '');
        $this->assertTrue($dto->isSuccess());
    }

    public function test_is_success_false_for_300(): void
    {
        $dto = new ApiResponseDTO(300, '');
        $this->assertFalse($dto->isSuccess());
    }

    public function test_is_success_false_for_404(): void
    {
        $dto = new ApiResponseDTO(404, '');
        $this->assertFalse($dto->isSuccess());
    }

    public function test_is_success_false_for_500(): void
    {
        $dto = new ApiResponseDTO(500, '');
        $this->assertFalse($dto->isSuccess());
    }

    // --- isClientError() ---

    public function test_is_client_error_true_for_400(): void
    {
        $dto = new ApiResponseDTO(400, '');
        $this->assertTrue($dto->isClientError());
    }

    public function test_is_client_error_true_for_404(): void
    {
        $dto = new ApiResponseDTO(404, '');
        $this->assertTrue($dto->isClientError());
    }

    public function test_is_client_error_true_for_499(): void
    {
        $dto = new ApiResponseDTO(499, '');
        $this->assertTrue($dto->isClientError());
    }

    public function test_is_client_error_false_for_200(): void
    {
        $dto = new ApiResponseDTO(200, '');
        $this->assertFalse($dto->isClientError());
    }

    public function test_is_client_error_false_for_500(): void
    {
        $dto = new ApiResponseDTO(500, '');
        $this->assertFalse($dto->isClientError());
    }

    // --- isServerError() ---

    public function test_is_server_error_true_for_500(): void
    {
        $dto = new ApiResponseDTO(500, '');
        $this->assertTrue($dto->isServerError());
    }

    public function test_is_server_error_true_for_503(): void
    {
        $dto = new ApiResponseDTO(503, '');
        $this->assertTrue($dto->isServerError());
    }

    public function test_is_server_error_false_for_499(): void
    {
        $dto = new ApiResponseDTO(499, '');
        $this->assertFalse($dto->isServerError());
    }

    public function test_is_server_error_false_for_200(): void
    {
        $dto = new ApiResponseDTO(200, '');
        $this->assertFalse($dto->isServerError());
    }

    // --- json() ---

    public function test_json_decodes_valid_json_object(): void
    {
        $dto = new ApiResponseDTO(200, '{"name":"Alice","age":30}');

        $result = $dto->json();

        $this->assertSame(['name' => 'Alice', 'age' => 30], $result);
    }

    public function test_json_decodes_valid_json_array(): void
    {
        $dto = new ApiResponseDTO(200, '[1,2,3]');

        $result = $dto->json();

        $this->assertSame([1, 2, 3], $result);
    }

    public function test_json_throws_on_invalid_json(): void
    {
        $dto = new ApiResponseDTO(200, 'not-json');

        $this->expectException(\JsonException::class);
        $dto->json();
    }

    public function test_json_decodes_null_literal(): void
    {
        $dto = new ApiResponseDTO(200, 'null');

        $this->assertNull($dto->json());
    }
}
