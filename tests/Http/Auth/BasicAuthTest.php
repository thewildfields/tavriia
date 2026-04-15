<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http\Auth;

use TheWildFields\Tavriia\Http\Auth\BasicAuth;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class BasicAuthTest extends TestCase
{
    public function test_apply_to_adds_authorization_header(): void
    {
        $auth = new BasicAuth('user', 'secret');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $expected = 'Basic ' . base64_encode('user:secret');
        $this->assertSame($expected, $result->build()->headers['Authorization']);
    }

    public function test_apply_to_encodes_credentials_correctly(): void
    {
        $auth = new BasicAuth('admin', 'p@ssw0rd!');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $expected = 'Basic ' . base64_encode('admin:p@ssw0rd!');
        $this->assertSame($expected, $result->build()->headers['Authorization']);
    }

    public function test_apply_to_handles_empty_password(): void
    {
        $auth = new BasicAuth('user', '');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $expected = 'Basic ' . base64_encode('user:');
        $this->assertSame($expected, $result->build()->headers['Authorization']);
    }

    public function test_apply_to_is_immutable(): void
    {
        $auth = new BasicAuth('user', 'pass');
        $builder = new RequestBuilder('https://example.com');

        $result = $auth->applyTo($builder);

        $this->assertNotSame($builder, $result);
        $this->assertArrayNotHasKey('Authorization', $builder->build()->headers);
    }

    public function test_apply_to_url_returns_unchanged_url(): void
    {
        $auth = new BasicAuth('user', 'pass');
        $url = 'https://example.com/api/endpoint';

        $result = $auth->applyToUrl($url);

        $this->assertSame($url, $result);
    }
}
