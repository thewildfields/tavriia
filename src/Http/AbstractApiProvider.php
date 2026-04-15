<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http;

use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;
use TheWildFields\Tavriia\Dto\ApiResponseDto;
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

/**
 * Abstract base class for API providers.
 *
 * Consuming plugins extend this class to define external API services
 * with base URL, authentication, and default settings.
 *
 * Example:
 *
 *   final class StripeProvider extends AbstractApiProvider
 *   {
 *       protected function configure(): ApiProviderConfigDto
 *       {
 *           return new ApiProviderConfigDto(
 *               baseUrl: 'https://api.stripe.com/v1',
 *               auth: new BearerTokenAuth(get_option('stripe_secret_key')),
 *           );
 *       }
 *
 *       public function createPaymentIntent(int $amount): array
 *       {
 *           return $this->post('/payment_intents', ['amount' => $amount])->json();
 *       }
 *   }
 */
abstract class AbstractApiProvider
{
    private readonly ApiProviderConfigDto $config;

    public function __construct(
        private readonly HttpClient $httpClient,
    ) {
        $this->config = $this->configure();
    }

    /**
     * Configure the API provider.
     *
     * Implementations must return an ApiProviderConfigDto defining
     * the base URL, authentication strategy, and default settings.
     */
    abstract protected function configure(): ApiProviderConfigDto;

    /**
     * Send a GET request to the given endpoint.
     *
     * @param string               $endpoint    API endpoint (relative to base URL).
     * @param array<string, mixed> $queryParams Query parameters to append.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function get(string $endpoint, array $queryParams = []): ApiResponseDto
    {
        return $this->request('GET', $endpoint, ['query' => $queryParams]);
    }

    /**
     * Send a POST request to the given endpoint.
     *
     * @param string               $endpoint API endpoint (relative to base URL).
     * @param array<string, mixed> $data     Request body data.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function post(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->request('POST', $endpoint, ['body' => $data]);
    }

    /**
     * Send a PUT request to the given endpoint.
     *
     * @param string               $endpoint API endpoint (relative to base URL).
     * @param array<string, mixed> $data     Request body data.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function put(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->request('PUT', $endpoint, ['body' => $data]);
    }

    /**
     * Send a PATCH request to the given endpoint.
     *
     * @param string               $endpoint API endpoint (relative to base URL).
     * @param array<string, mixed> $data     Request body data.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function patch(string $endpoint, array $data = []): ApiResponseDto
    {
        return $this->request('PATCH', $endpoint, ['body' => $data]);
    }

    /**
     * Send a DELETE request to the given endpoint.
     *
     * @param string $endpoint API endpoint (relative to base URL).
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function delete(string $endpoint): ApiResponseDto
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Send a request with the given method and options.
     *
     * @param string               $method   HTTP method.
     * @param string               $endpoint API endpoint (relative to base URL).
     * @param array<string, mixed> $options  Request options (query, body, json, headers).
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    protected function request(string $method, string $endpoint, array $options = []): ApiResponseDto
    {
        $url = $this->buildUrl($endpoint, $options['query'] ?? []);
        $builder = $this->createRequestBuilder($url)->method($method);

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $builder = $builder->header($name, $value);
            }
        }

        if (isset($options['json'])) {
            $builder = $builder->jsonBody($options['json']);
        } elseif (isset($options['body'])) {
            $builder = $builder->body($options['body']);
        }

        return $this->httpClient->request($builder->build());
    }

    /**
     * Build the full URL from base URL, endpoint, and query parameters.
     *
     * @param string               $endpoint    API endpoint.
     * @param array<string, mixed> $queryParams Query parameters.
     */
    private function buildUrl(string $endpoint, array $queryParams = []): string
    {
        $baseUrl = rtrim($this->config->baseUrl, '/');
        $endpoint = '/' . ltrim($endpoint, '/');
        $url = $baseUrl . $endpoint;

        if ($queryParams !== []) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($queryParams);
        }

        return $this->config->auth->applyToUrl($url);
    }

    /**
     * Create a RequestBuilder with provider defaults applied.
     */
    private function createRequestBuilder(string $url): RequestBuilder
    {
        $builder = new RequestBuilder($url);
        $builder = $builder
            ->timeout($this->config->timeout)
            ->sslVerify($this->config->sslVerify);

        foreach ($this->config->defaultHeaders as $name => $value) {
            $builder = $builder->header($name, $value);
        }

        return $this->config->auth->applyTo($builder);
    }
}
