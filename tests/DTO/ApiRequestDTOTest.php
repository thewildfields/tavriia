<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use TheWildFields\Tavriia\DTO\ApiRequestDTO;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiRequestDTOTest extends TestCase
{
    public function test_construction_with_url_only(): void
    {
        $dto = new ApiRequestDTO(url: 'https://example.com/api');

        $this->assertSame('https://example.com/api', $dto->url);
        $this->assertSame('GET', $dto->method);
        $this->assertSame([], $dto->headers);
        $this->assertSame([], $dto->body);
        $this->assertSame(15, $dto->timeout);
        $this->assertTrue($dto->sslVerify);
        $this->assertSame([], $dto->extraArgs);
    }

    public function test_construction_with_all_params(): void
    {
        $dto = new ApiRequestDTO(
            url: 'https://api.example.com/endpoint',
            method: 'POST',
            headers: ['Authorization' => 'Bearer token', 'Content-Type' => 'application/json'],
            body: ['key' => 'value'],
            timeout: 30,
            sslVerify: false,
            extraArgs: ['blocking' => false],
        );

        $this->assertSame('https://api.example.com/endpoint', $dto->url);
        $this->assertSame('POST', $dto->method);
        $this->assertSame(['Authorization' => 'Bearer token', 'Content-Type' => 'application/json'], $dto->headers);
        $this->assertSame(['key' => 'value'], $dto->body);
        $this->assertSame(30, $dto->timeout);
        $this->assertFalse($dto->sslVerify);
        $this->assertSame(['blocking' => false], $dto->extraArgs);
    }

    public function test_to_wp_args_returns_correct_structure(): void
    {
        $dto = new ApiRequestDTO(
            url: 'https://example.com',
            method: 'POST',
            headers: ['Authorization' => 'Bearer abc'],
            body: ['foo' => 'bar'],
            timeout: 20,
            sslVerify: true,
        );

        $args = $dto->toWpArgs();

        $this->assertSame('POST', $args['method']);
        $this->assertSame(['Authorization' => 'Bearer abc'], $args['headers']);
        $this->assertSame(['foo' => 'bar'], $args['body']);
        $this->assertSame(20, $args['timeout']);
        $this->assertTrue($args['sslverify']);
    }

    public function test_to_wp_args_uses_ssl_verify_key_not_ssl_verify(): void
    {
        $dto = new ApiRequestDTO(url: 'https://example.com', sslVerify: false);

        $args = $dto->toWpArgs();

        $this->assertArrayHasKey('sslverify', $args);
        $this->assertArrayNotHasKey('sslVerify', $args);
        $this->assertFalse($args['sslverify']);
    }

    public function test_to_wp_args_merges_extra_args_with_standard_args(): void
    {
        $dto = new ApiRequestDTO(
            url: 'https://example.com',
            method: 'GET',
            extraArgs: ['blocking' => false, 'redirection' => 3],
        );

        $args = $dto->toWpArgs();

        $this->assertArrayHasKey('blocking', $args);
        $this->assertFalse($args['blocking']);
        $this->assertSame(3, $args['redirection']);
        $this->assertSame('GET', $args['method']);
    }

    public function test_to_wp_args_standard_args_override_extra_args(): void
    {
        // Standard keys should override anything in extraArgs with the same name
        $dto = new ApiRequestDTO(
            url: 'https://example.com',
            method: 'DELETE',
            extraArgs: ['method' => 'PATCH'],
        );

        $args = $dto->toWpArgs();

        $this->assertSame('DELETE', $args['method']);
    }

    public function test_body_can_be_raw_string(): void
    {
        $dto = new ApiRequestDTO(
            url: 'https://example.com',
            body: '{"raw":"json"}',
        );

        $this->assertSame('{"raw":"json"}', $dto->body);
        $args = $dto->toWpArgs();
        $this->assertSame('{"raw":"json"}', $args['body']);
    }
}
