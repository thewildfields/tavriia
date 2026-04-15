<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;
use TheWildFields\Tavriia\Dto\ApiResponseDto;
use TheWildFields\Tavriia\Http\AbstractApiProvider;
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;
use TheWildFields\Tavriia\Http\Auth\QueryParamAuth;
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;
use TheWildFields\Tavriia\Tests\TestCase;

/**
 * Tests for AbstractApiProvider.
 *
 * Uses a concrete test implementation (TestApiProvider) to verify
 * the abstract base class behaviour.
 */
final class AbstractApiProviderTest extends TestCase
{
    private HttpClient $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new HttpClient(new ResponseProcessor());
    }

    private function stubProcessorSuccess(int $code = 200, string $body = '{"ok":true}'): void
    {
        Functions\when('wp_remote_retrieve_response_code')->justReturn($code);
        Functions\when('wp_remote_retrieve_body')->justReturn($body);
        Functions\when('wp_remote_retrieve_headers')->justReturn([]);
    }

    // --- URL building ---

    public function test_get_builds_url_from_base_and_endpoint(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('https://api.example.com/v1/users', $url);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('/users');
    }

    public function test_get_normalizes_endpoint_slashes(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('https://api.example.com/v1/users', $url);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('users'); // No leading slash
    }

    public function test_get_appends_query_params(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertStringContainsString('page=1', $url);
                $this->assertStringContainsString('limit=10', $url);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('/users', ['page' => 1, 'limit' => 10]);
    }

    // --- Authentication ---

    public function test_bearer_auth_adds_authorization_header(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('Bearer secret-token', $args['headers']['Authorization']);
                return [];
            });

        $provider = new BearerAuthProvider($this->httpClient);
        $provider->publicGet('/endpoint');
    }

    public function test_query_param_auth_appends_to_url(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertStringContainsString('key=my-api-key', $url);
                return [];
            });

        $provider = new QueryParamAuthProvider($this->httpClient);
        $provider->publicGet('/endpoint');
    }

    // --- Default headers ---

    public function test_default_headers_are_applied(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('application/json', $args['headers']['Accept']);
                $this->assertSame('v2', $args['headers']['X-API-Version']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('/endpoint');
    }

    // --- Timeout and SSL ---

    public function test_timeout_is_applied(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame(30, $args['timeout']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('/endpoint');
    }

    public function test_ssl_verify_is_applied(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertTrue($args['sslverify']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicGet('/endpoint');
    }

    // --- HTTP methods ---

    public function test_post_sends_post_method(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('POST', $args['method']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicPost('/users', ['name' => 'John']);
    }

    public function test_post_sends_body_data(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame(['name' => 'John'], $args['body']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicPost('/users', ['name' => 'John']);
    }

    public function test_put_sends_put_method(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('PUT', $args['method']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicPut('/users/1', ['name' => 'Jane']);
    }

    public function test_patch_sends_patch_method(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('PATCH', $args['method']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicPatch('/users/1', ['status' => 'active']);
    }

    public function test_delete_sends_delete_method(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('DELETE', $args['method']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicDelete('/users/1');
    }

    // --- Request with options ---

    public function test_request_with_json_body(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('{"name":"John"}', $args['body']);
                $this->assertSame('application/json', $args['headers']['Content-Type']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicRequest('POST', '/users', [
            'json' => ['name' => 'John'],
        ]);
    }

    public function test_request_with_additional_headers(): void
    {
        $this->stubProcessorSuccess();

        Functions\expect('wp_remote_request')
            ->once()
            ->andReturnUsing(function ($url, $args) {
                $this->assertSame('custom-value', $args['headers']['X-Custom']);
                return [];
            });

        $provider = new TestApiProvider($this->httpClient);
        $provider->publicRequest('GET', '/endpoint', [
            'headers' => ['X-Custom' => 'custom-value'],
        ]);
    }

    // --- Response ---

    public function test_get_returns_api_response_dto(): void
    {
        $this->stubProcessorSuccess(200, '{"id":1,"name":"John"}');
        Functions\when('wp_remote_request')->justReturn([]);

        $provider = new TestApiProvider($this->httpClient);
        $result = $provider->publicGet('/users/1');

        $this->assertInstanceOf(ApiResponseDto::class, $result);
        $this->assertSame(200, $result->statusCode);
        $this->assertSame('{"id":1,"name":"John"}', $result->body);
    }
}

// --- Test implementations ---

/**
 * Concrete test implementation of AbstractApiProvider.
 */
final class TestApiProvider extends AbstractApiProvider
{
    protected function configure(): ApiProviderConfigDto
    {
        return new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com/v1',
            defaultHeaders: [
                'Accept' => 'application/json',
                'X-API-Version' => 'v2',
            ],
            timeout: 30,
        );
    }

    public function publicGet(string $endpoint, array $queryParams = []): ApiResponseDto
    {
        return $this->get($endpoint, $queryParams);
    }

    public function publicPost(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->post($endpoint, $data);
    }

    public function publicPut(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->put($endpoint, $data);
    }

    public function publicPatch(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->patch($endpoint, $data);
    }

    public function publicDelete(string $endpoint): ApiResponseDto
    {
        return $this->delete($endpoint);
    }

    public function publicRequest(string $method, string $endpoint, array $options = []): ApiResponseDto
    {
        return $this->request($method, $endpoint, $options);
    }
}

/**
 * Test provider with bearer token auth.
 */
final class BearerAuthProvider extends AbstractApiProvider
{
    protected function configure(): ApiProviderConfigDto
    {
        return new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            auth: new BearerTokenAuth('secret-token'),
        );
    }

    public function publicGet(string $endpoint): ApiResponseDto
    {
        return $this->get($endpoint);
    }
}

/**
 * Test provider with query param auth.
 */
final class QueryParamAuthProvider extends AbstractApiProvider
{
    protected function configure(): ApiProviderConfigDto
    {
        return new ApiProviderConfigDto(
            baseUrl: 'https://api.example.com',
            auth: new QueryParamAuth('my-api-key', 'key'),
        );
    }

    public function publicGet(string $endpoint): ApiResponseDto
    {
        return $this->get($endpoint);
    }
}
