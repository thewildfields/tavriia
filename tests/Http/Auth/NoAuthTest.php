<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http\Auth;

use TheWildFields\Tavriia\Http\Auth\NoAuth;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class NoAuthTest extends TestCase
{
    public function test_apply_to_returns_unchanged_builder(): void
    {
        $auth = new NoAuth();
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame($builder, $result);
    }

    public function test_apply_to_does_not_add_headers(): void
    {
        $auth = new NoAuth();
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame([], $result->build()->headers);
    }

    public function test_apply_to_url_returns_unchanged_url(): void
    {
        $auth = new NoAuth();
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }

    public function test_apply_to_url_preserves_existing_query_params(): void
    {
        $auth = new NoAuth();
        $url = 'https://example.com/api?page=1&limit=10';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }
}
