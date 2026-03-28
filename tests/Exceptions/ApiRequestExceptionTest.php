<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Exceptions;

use Mockery;
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Tests\TestCase;

final class ApiRequestExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $e = ApiRequestException::forUrl('https://example.com', 'timeout');

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_from_wp_error_message_contains_error_code(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('http_request_failed');
        $wpError->shouldReceive('get_error_message')->andReturn('A valid URL was not provided.');

        $e = ApiRequestException::fromWpError($wpError);

        $this->assertStringContainsString('http_request_failed', $e->getMessage());
    }

    public function test_from_wp_error_message_contains_error_message(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('http_request_failed');
        $wpError->shouldReceive('get_error_message')->andReturn('A valid URL was not provided.');

        $e = ApiRequestException::fromWpError($wpError);

        $this->assertStringContainsString('A valid URL was not provided.', $e->getMessage());
    }

    public function test_from_wp_error_message_format(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('curl_error');
        $wpError->shouldReceive('get_error_message')->andReturn('cURL error 28: Timeout');

        $e = ApiRequestException::fromWpError($wpError);

        $this->assertSame('HTTP request failed: [curl_error] cURL error 28: Timeout', $e->getMessage());
    }

    public function test_from_wp_error_returns_api_request_exception(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('code');
        $wpError->shouldReceive('get_error_message')->andReturn('message');

        $e = ApiRequestException::fromWpError($wpError);

        $this->assertInstanceOf(ApiRequestException::class, $e);
    }

    public function test_for_url_message_contains_url(): void
    {
        $e = ApiRequestException::forUrl('https://api.example.com/data', 'timeout exceeded');

        $this->assertStringContainsString('https://api.example.com/data', $e->getMessage());
    }

    public function test_for_url_message_contains_reason(): void
    {
        $e = ApiRequestException::forUrl('https://example.com', 'connection refused');

        $this->assertStringContainsString('connection refused', $e->getMessage());
    }

    public function test_for_url_message_format(): void
    {
        $e = ApiRequestException::forUrl('https://example.com/api', 'SSL handshake failed');

        $this->assertSame('HTTP request to "https://example.com/api" failed: SSL handshake failed', $e->getMessage());
    }

    public function test_for_url_returns_api_request_exception(): void
    {
        $e = ApiRequestException::forUrl('https://example.com', 'reason');

        $this->assertInstanceOf(ApiRequestException::class, $e);
    }
}
