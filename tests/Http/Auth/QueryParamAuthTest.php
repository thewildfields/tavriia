<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http\Auth;

use TheWildFields\Tavriia\Http\Auth\QueryParamAuth;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class QueryParamAuthTest extends TestCase
{
    public function test_apply_to_url_appends_default_param(): void
    {
        $auth = new QueryParamAuth('my-api-key');
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame('https://example.com/api/endpoint?key=my-api-key', $result);
    }

    public function test_apply_to_url_uses_custom_param_name(): void
    {
        $auth = new QueryParamAuth('my-api-key', 'api_key');
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame('https://example.com/api/endpoint?api_key=my-api-key', $result);
    }

    public function test_apply_to_url_appends_to_existing_query_params(): void
    {
        $auth = new QueryParamAuth('my-api-key');
        $url = 'https://example.com/api/endpoint?page=1';

        $result = $auth->applyToUrl($url);

        $this->assertSame('https://example.com/api/endpoint?page=1&key=my-api-key', $result);
    }

    public function test_apply_to_url_encodes_special_characters(): void
    {
        $auth = new QueryParamAuth('key=value&other', 'auth');
        $url = 'https://example.com/api';

        $result = $auth->applyToUrl($url);

        $this->assertStringContainsString('auth=key%3Dvalue%26other', $result);
    }

    public function test_apply_to_returns_unchanged_builder(): void
    {
        $auth = new QueryParamAuth('key');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame($builder, $result);
    }

    public function test_apply_to_does_not_add_headers(): void
    {
        $auth = new QueryParamAuth('key');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertSame([], $result->build()->headers);
    }
}
