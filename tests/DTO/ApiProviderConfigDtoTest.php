<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Dto;

use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;
use TheWildFields\Tavriia\Http\Auth\AuthStrategyInterface;
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;
use TheWildFields\Tavriia\Http\Auth\NoAuth;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiProviderConfigDtoTest extends TestCase
{
    public function test_constructor_sets_base_url(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
        );

        $this->assertSame('https://api.example.com', $dto->baseUrl);
    }

    public function test_default_auth_is_no_auth(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
        );

        $this->assertInstanceOf(NoAuth::class, $dto->auth);
    }

    public function test_constructor_accepts_custom_auth(): void
    {
        $auth = new BearerTokenAuth('token');
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            auth: $auth,
        );

        $this->assertSame($auth, $dto->auth);
    }

    public function test_default_headers_is_empty_array(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
        );

        $this->assertSame([], $dto->defaultHeaders);
    }

    public function test_constructor_accepts_default_headers(): void
    {
        $headers = ['Accept' => 'application/json', 'X-Version' => '2'];
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            defaultHeaders: $headers,
        );

        $this->assertSame($headers, $dto->defaultHeaders);
    }

    public function test_default_timeout_is_15(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
        );

        $this->assertSame(15, $dto->timeout);
    }

    public function test_constructor_accepts_custom_timeout(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            timeout: 30,
        );

        $this->assertSame(30, $dto->timeout);
    }

    public function test_default_ssl_verify_is_true(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
        );

        $this->assertTrue($dto->sslVerify);
    }

    public function test_constructor_accepts_ssl_verify_false(): void
    {
        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            sslVerify: false,
        );

        $this->assertFalse($dto->sslVerify);
    }

    public function test_full_configuration(): void
    {
        $auth = new BearerTokenAuth('secret');
        $headers = ['Accept' => 'application/json'];

        $dto = new ApiProviderConfigDto(
            baseUrl: 'https://api.stripe.com/v1',
            auth: $auth,
            defaultHeaders: $headers,
            timeout: 60,
            sslVerify: false,
        );

        $this->assertSame('https://api.stripe.com/v1', $dto->baseUrl);
        $this->assertSame($auth, $dto->auth);
        $this->assertSame($headers, $dto->defaultHeaders);
        $this->assertSame(60, $dto->timeout);
        $this->assertFalse($dto->sslVerify);
    }
}
