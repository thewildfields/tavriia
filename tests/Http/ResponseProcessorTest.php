<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\DTO\ApiResponseDTO;
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;
use TheWildFields\Tavriia\Http\ResponseProcessor;
use TheWildFields\Tavriia\Tests\TestCase;

final class ResponseProcessorTest extends TestCase
{
    private ResponseProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new ResponseProcessor();
    }

    public function test_process_throws_api_request_exception_on_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('http_request_failed');
        $wpError->shouldReceive('get_error_message')->andReturn('Could not resolve host');

        $this->expectException(ApiRequestException::class);
        $this->expectExceptionMessage('http_request_failed');

        $this->processor->process($wpError);
    }

    public function test_process_returns_api_response_dto_on_success(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('{"success":true}');
        Functions\when('wp_remote_retrieve_headers')->justReturn(['content-type' => 'application/json']);

        $rawResponse = ['response' => ['code' => 200], 'body' => '{"success":true}'];

        $result = $this->processor->process($rawResponse);

        $this->assertInstanceOf(ApiResponseDTO::class, $result);
        $this->assertSame(200, $result->statusCode);
        $this->assertSame('{"success":true}', $result->body);
    }

    public function test_process_throws_api_response_exception_on_404(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(404);
        Functions\when('wp_remote_retrieve_body')->justReturn('Not Found');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $rawResponse = ['response' => ['code' => 404], 'body' => 'Not Found'];

        $this->expectException(ApiResponseException::class);

        $this->processor->process($rawResponse);
    }

    public function test_process_throws_api_response_exception_on_500(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        Functions\when('wp_remote_retrieve_body')->justReturn('Internal Server Error');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $rawResponse = ['response' => ['code' => 500], 'body' => 'Internal Server Error'];

        $this->expectException(ApiResponseException::class);

        $this->processor->process($rawResponse);
    }

    public function test_process_includes_response_dto_in_exception(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(422);
        Functions\when('wp_remote_retrieve_body')->justReturn('Validation failed');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $rawResponse = [];

        try {
            $this->processor->process($rawResponse);
            $this->fail('Expected ApiResponseException was not thrown.');
        } catch (ApiResponseException $e) {
            $this->assertSame(422, $e->getResponse()->statusCode);
            $this->assertSame('Validation failed', $e->getResponse()->body);
        }
    }

    public function test_process_normalizes_headers_from_array(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('ok');
        Functions\when('wp_remote_retrieve_headers')->justReturn(['X-Custom' => 'value']);

        $result = $this->processor->process([]);

        $this->assertSame('value', $result->headers['X-Custom']);
    }

    public function test_process_handles_empty_headers(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn('');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $result = $this->processor->process([]);

        $this->assertSame([], $result->headers);
    }

    public function test_process_201_is_considered_success(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(201);
        Functions\when('wp_remote_retrieve_body')->justReturn('Created');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $result = $this->processor->process([]);

        $this->assertSame(201, $result->statusCode);
    }

    public function test_process_400_throws_api_response_exception(): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn(400);
        Functions\when('wp_remote_retrieve_body')->justReturn('Bad Request');
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);

        $this->expectException(ApiResponseException::class);

        $this->processor->process([]);
    }
}
