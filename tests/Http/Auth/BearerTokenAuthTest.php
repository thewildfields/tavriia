<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http\Auth;

use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class BearerTokenAuthTest extends TestCase
{
    public function test_apply_to_adds_authorization_header(): void
    {
        $auth = new BearerTokenAuth('my-secret-token');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame(
            'Bearer my-secret-token',
            $result->build()->headers['Authorization']
        );
    }

    public function test_apply_to_is_immutable(): void
    {
        $auth = new BearerTokenAuth('token');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertNotSame($builder, $result);
        $this->assertArrayNotHasKey('Authorization', $builder->build()->headers);
    }

    public function test_apply_to_url_returns_unchanged_url(): void
    {
        $auth = new BearerTokenAuth('token');
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }

    public function test_apply_to_url_preserves_existing_query_params(): void
    {
        $auth = new BearerTokenAuth('token');
        $url = 'https://example.com/api?page=1';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }
}
