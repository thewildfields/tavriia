<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http\Auth;

use TheWildFields\Tavriia\Http\Auth\ApiKeyAuth;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiKeyAuthTest extends TestCase
{
    public function test_apply_to_adds_default_header(): void
    {
        $auth = new ApiKeyAuth('my-api-key');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame('my-api-key', $result->build()->headers['X-API-Key']);
    }

    public function test_apply_to_uses_custom_header_name(): void
    {
        $auth = new ApiKeyAuth('my-api-key', 'X-Custom-Auth');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame('my-api-key', $result->build()->headers['X-Custom-Auth']);
        $this->assertArrayNotHasKey('X-API-Key', $result->build()->headers);
    }

    public function test_apply_to_is_immutable(): void
    {
        $auth = new ApiKeyAuth('key');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertNotSame($builder, $result);
        $this->assertArrayNotHasKey('X-API-Key', $builder->build()->headers);
    }

    public function test_apply_to_url_returns_unchanged_url(): void
    {
        $auth = new ApiKeyAuth('key');
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }
}
