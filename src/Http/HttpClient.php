<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http;

use TheWildFields\Tavriia\Contracts\ApiClientInterface;
use TheWildFields\Tavriia\Dto\ApiRequestDto;
use TheWildFields\Tavriia\Dto\ApiResponseDto;
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

/**
 * WordPress-backed HTTP client.
 *
 * Wraps wp_remote_get(), wp_remote_post(), and wp_remote_request().
 * All WP_Error instances are converted to typed exceptions at this boundary —
 * no WP_Error ever escapes this class.
 */
final class HttpClient implements ApiClientInterface
{
    public function __construct(
        private readonly ResponseProcessor $responseProcessor,
    ) {}

    /**
     * Send a GET request.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    public function get(string $url, array $args = []): ApiResponseDto
    {
        $rawResponse = wp_remote_get($url, $args);

        return $this->responseProcessor->process($rawResponse);
    }

    /**
     * Send a POST request.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    public function post(string $url, array $args = []): ApiResponseDto
    {
        $rawResponse = wp_remote_post($url, $args);

        return $this->responseProcessor->process($rawResponse);
    }

    /**
     * Send an arbitrary request described by an ApiRequestDTO.
     *
     * @throws ApiRequestException  On transport failure.
     * @throws ApiResponseException On non-2xx HTTP status.
     */
    public function request(ApiRequestDto $request): ApiResponseDto
    {
        $rawResponse = wp_remote_request($request->url, $request->toWpArgs());

        return $this->responseProcessor->process($rawResponse);
    }
}
