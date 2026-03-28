<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\DTO\ApiRequestDTO;
use TheWildFields\Tavriia\DTO\ApiResponseDTO;
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;
use TheWildFields\Tavriia\Tests\TestCase;

/**
 * Tests for HttpClient.
 *
 * ResponseProcessor is a final class, making it unmockable with standard
 * Mockery without a code-generation extension. We therefore use a real
 * ResponseProcessor and mock the WordPress functions it depends on
 * (wp_remote_retrieve_response_code, wp_remote_retrieve_body,
 * wp_remote_retrieve_headers) to control its behaviour from these tests.
 */
final class HttpClientTest extends TestCase
{
    private ResponseProcessor $responseProcessor;
    private HttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseProcessor = new ResponseProcessor();
        $this->client            = new HttpClient($this->responseProcessor);
    }

    /**
     * Set up Brain Monkey stubs so ResponseProcessor returns a success DTO.
     */
    private function stubProcessorSuccess(int $code = 200, string $body = '{"ok":true}'): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn($code);
        Functions\when('wp_remote_retrieve_body')->justReturn($body);
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);
    }

    // --- get ---

    public function test_get_calls_wp_remote_get_with_correct_url(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_get')
            ->once()
            ->with('https://example.com/api', [])
            ->andReturn(['response' => ['code' => 200], 'body' => '']);

        $result = $this->client->get('https://example.com/api');

        $this->assertInstanceOf(ApiResponseDTO::class, $result);
    }

    public function test_get_passes_args_to_wp_remote_get(): void
    {
        $this->stubProcessorSuccess();

        $args = ['timeout' => 30, 'headers' => ['Accept' => 'application/json']];

        Functions\expect('wp_remote_get')
            ->once()
            ->with('https://example.com', $args)
            ->andReturn([]);

        $this->client->get('https://example.com', $args);
    }

    public function test_get_returns_api_response_dto_on_success(): void
    {
        Functions\when('wp_remote_get')->justReturn([]);
        $this->stubProcessorSuccess(200, '{"hello":"world"}');

        $result = $this->client->get('https://example.com');

        $this->assertSame(200, $result->statusCode);
        $this->assertSame('{"hello":"world"}', $result->body);
    }

    public function test_get_default_args_are_empty(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_get')
            ->once()
            ->with('https://example.com', [])
            ->andReturn([]);

        $this->client->get('https://example.com');
    }

    // --- post ---

    public function test_post_calls_wp_remote_post_with_correct_url(): void
    {
        $this->stubProcessorSuccess(201, 'Created');

        Functions\expect('wp_remote_post')
            ->once()
            ->with('https://example.com/submit', [])
            ->andReturn([]);

        $result = $this->client->post('https://example.com/submit');

        $this->assertInstanceOf(ApiResponseDTO::class, $result);
    }

    public function test_post_passes_args_to_wp_remote_post(): void
    {
        $this->stubProcessorSuccess();

        $args = ['body' => ['key' => 'val']];

        Functions\expect('wp_remote_post')
            ->once()
            ->with('https://example.com', $args)
            ->andReturn([]);

        $this->client->post('https://example.com', $args);
    }

    public function test_post_returns_api_response_dto_on_success(): void
    {
        Functions\when('wp_remote_post')->justReturn([]);
        $this->stubProcessorSuccess(201, '');

        $result = $this->client->post('https://example.com');

        $this->assertSame(201, $result->statusCode);
    }

    // --- request ---

    public function test_request_calls_wp_remote_request_with_dto_url(): void
    {
        $this->stubProcessorSuccess();

        $dto = new ApiRequestDTO(
            url: 'https://example.com/resource',
            method: 'PUT',
        );

        Functions\expect('wp_remote_request')
            ->once()
            ->with('https://example.com/resource', $dto->toWpArgs())
            ->andReturn([]);

        $result = $this->client->request($dto);

        $this->assertInstanceOf(ApiResponseDTO::class, $result);
    }

    public function test_request_passes_dto_wp_args_to_wp_remote_request(): void
    {
        $this->stubProcessorSuccess();

        $dto = new ApiRequestDTO(
            url: 'https://example.com/resource',
            method: 'DELETE',
            headers: ['X-Custom' => 'header'],
            timeout: 45,
            sslVerify: false,
        );

        Functions\expect('wp_remote_request')
            ->once()
            ->with('https://example.com/resource', $dto->toWpArgs())
            ->andReturn([]);

        $this->client->request($dto);
    }

    public function test_request_returns_api_response_dto(): void
    {
        Functions\when('wp_remote_request')->justReturn([]);
        $this->stubProcessorSuccess(200, 'ok');

        $dto    = new ApiRequestDTO(url: 'https://example.com');
        $result = $this->client->request($dto);

        $this->assertSame(200, $result->statusCode);
    }
}
