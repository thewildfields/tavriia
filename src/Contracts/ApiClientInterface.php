<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\DTO\ApiRequestDTO;
use TheWildFields\Tavriia\DTO\ApiResponseDTO;

/**
 * Contract for HTTP API clients built on top of WordPress remote request functions.
 */
interface ApiClientInterface
{
    /**
     * Send a GET request.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\ApiRequestException  When the request itself fails (WP_Error).
     * @throws \TheWildFields\Tavriia\Exceptions\ApiResponseException When the response indicates an error.
     */
    public function get(string $url, array $args = []): ApiResponseDTO;

    /**
     * Send a POST request.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\ApiRequestException  When the request itself fails (WP_Error).
     * @throws \TheWildFields\Tavriia\Exceptions\ApiResponseException When the response indicates an error.
     */
    public function post(string $url, array $args = []): ApiResponseDTO;

    /**
     * Send an arbitrary request described by an ApiRequestDTO.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\ApiRequestException  When the request itself fails (WP_Error).
     * @throws \TheWildFields\Tavriia\Exceptions\ApiResponseException When the response indicates an error.
     */
    public function request(ApiRequestDTO $request): ApiResponseDTO;
}
