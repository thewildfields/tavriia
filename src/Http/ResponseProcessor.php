<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Http;

use TheWildFields\Tavriia\Dto\ApiResponseDto;
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

use ArrayAccess;

use WP_Error;

/**
 * Converts a raw WordPress HTTP response into a typed ApiResponseDto.
 *
 * Any WP_Error at the transport level is converted to ApiRequestException.
 * Error HTTP status codes (4xx, 5xx) are converted to ApiResponseException.
 */
final class ResponseProcessor
{
    /**
     * Process a raw WordPress HTTP response.
     *
     * @param WP_Error|array<string, mixed> $rawResponse The value returned by wp_remote_*().
     *
     * @throws ApiRequestException  When WordPress reports a transport-level error (WP_Error).
     * @throws ApiResponseException When the HTTP status code indicates a failure.
     */
    public function process(WP_Error|array $rawResponse): ApiResponseDto
    {
        if ($rawResponse instanceof WP_Error) {
            throw ApiRequestException::fromWpError($rawResponse);
        }

        $statusCode = (int) wp_remote_retrieve_response_code($rawResponse);
        $body       = (string) wp_remote_retrieve_body($rawResponse);
        $headers    = $this->normalizeHeaders(wp_remote_retrieve_headers($rawResponse));

        $response = new ApiResponseDto(
            statusCode: $statusCode,
            body: $body,
            headers: $headers,
        );

        if (!$response->isSuccess()) {
            throw ApiResponseException::forResponse($response);
        }

        return $response;
    }

    /**
     * Normalize response headers into a simple string-keyed array.
     *
     * wp_remote_retrieve_headers() may return a \Requests_Utility_CaseInsensitiveDictionary
     * or similar object; we coerce it to a plain array.
     *
     * @param ArrayAccess|iterable $rawHeaders
     * @return array<string, string>
     */
    private function normalizeHeaders(ArrayAccess|iterable $rawHeaders): array
    {
        if (is_array($rawHeaders)) {
            return array_map('strval', $rawHeaders);
        }

        if ($rawHeaders instanceof ArrayAccess || is_iterable($rawHeaders)) {
            $normalized = [];
            foreach ($rawHeaders as $key => $value) {
                $normalized[(string) $key] = (string) $value;
            }

            return $normalized;
        }

        return [];
    }
}
