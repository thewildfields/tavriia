<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\Dto\ApiRequestDto;
use TheWildFields\Tavriia\Dto\ApiResponseDto;

use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

/**
 * Contract for HTTP API clients built on top of WordPress remote request functions.
 */
interface ApiClientInterface
{
    /**
     * Send a GET request.
     *
     * @throws ApiRequestException  When the request itself fails (WP_Error).
     * @throws ApiResponseException When the response indicates an error.
     */
    public function get(string $url, array $args = []): ApiResponseDto;

    /**
     * Send a POST request.
     *
     * @throws ApiRequestException  When the request itself fails (WP_Error).
     * @throws ApiResponseException When the response indicates an error.
     */
    public function post(string $url, array $args = []): ApiResponseDto;

    /**
     * Send an arbitrary request described by an ApiRequestDto.
     *
     * @throws ApiRequestException  When the request itself fails (WP_Error).
     * @throws ApiResponseException When the response indicates an error.
     */
    public function request(ApiRequestDto $request): ApiResponseDto;
}
