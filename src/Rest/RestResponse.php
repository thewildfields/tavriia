<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Rest;

use WP_Error;
use WP_REST_Response;

/**
 * Typed response object for REST API callbacks.
 *
 * A RestResponse is the framework-native value plugin callbacks return.
 * At the WordPress boundary it is converted via toWp() into either a
 * WP_REST_Response (success) or a WP_Error (error) — the two types
 * WordPress' REST server understands. This keeps plugin code free of
 * direct WP_Error construction.
 *
 * Usage inside a REST callback:
 *   public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
 *   {
 *       try {
 *           $event = $this->repository->findById((int) $request['id']);
 *           return RestResponse::ok($event)->toWp();
 *       } catch (PostNotFoundException $e) {
 *           return RestResponse::notFound('event_not_found', $e->getMessage())->toWp();
 *       }
 *   }
 */
final class RestResponse
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $errorData
     */
    private function __construct(
        private readonly mixed $data,
        private readonly int $status,
        private readonly array $headers,
        private readonly bool $isError,
        private readonly string $errorCode,
        private readonly string $errorMessage,
        private readonly array $errorData,
    ) {}

    // -----------------------------------------------------------------
    // Success factories
    // -----------------------------------------------------------------

    /**
     * Build a 200 OK success response.
     *
     * @param array<string, string> $headers
     */
    public static function ok(mixed $data = null, array $headers = []): self
    {
        return new self(
            data: $data,
            status: 200,
            headers: $headers,
            isError: false,
            errorCode: '',
            errorMessage: '',
            errorData: [],
        );
    }

    /**
     * Build a 201 Created success response.
     *
     * @param array<string, string> $headers
     */
    public static function created(mixed $data = null, array $headers = []): self
    {
        return new self(
            data: $data,
            status: 201,
            headers: $headers,
            isError: false,
            errorCode: '',
            errorMessage: '',
            errorData: [],
        );
    }

    /**
     * Build a 204 No Content success response.
     */
    public static function noContent(): self
    {
        return new self(
            data: null,
            status: 204,
            headers: [],
            isError: false,
            errorCode: '',
            errorMessage: '',
            errorData: [],
        );
    }

    // -----------------------------------------------------------------
    // Error factories
    // -----------------------------------------------------------------

    /**
     * Build a generic error response with an explicit HTTP status.
     *
     * @param array<string, mixed> $data Additional error context passed to WP_Error.
     */
    public static function error(string $code, string $message, int $status = 400, array $data = []): self
    {
        return new self(
            data: null,
            status: $status,
            headers: [],
            isError: true,
            errorCode: $code,
            errorMessage: $message,
            errorData: $data,
        );
    }

    /**
     * Build a 400 Bad Request error response.
     *
     * @param array<string, mixed> $data
     */
    public static function badRequest(string $code, string $message, array $data = []): self
    {
        return self::error($code, $message, 400, $data);
    }

    /**
     * Build a 401 Unauthorized error response.
     *
     * @param array<string, mixed> $data
     */
    public static function unauthorized(string $code, string $message, array $data = []): self
    {
        return self::error($code, $message, 401, $data);
    }

    /**
     * Build a 403 Forbidden error response.
     *
     * @param array<string, mixed> $data
     */
    public static function forbidden(string $code, string $message, array $data = []): self
    {
        return self::error($code, $message, 403, $data);
    }

    /**
     * Build a 404 Not Found error response.
     *
     * @param array<string, mixed> $data
     */
    public static function notFound(string $code, string $message, array $data = []): self
    {
        return self::error($code, $message, 404, $data);
    }

    /**
     * Build a 500 Internal Server Error response.
     *
     * @param array<string, mixed> $data
     */
    public static function serverError(string $code, string $message, array $data = []): self
    {
        return self::error($code, $message, 500, $data);
    }

    // -----------------------------------------------------------------
    // WordPress boundary
    // -----------------------------------------------------------------

    /**
     * Convert this response to the native WordPress REST type.
     *
     * Returns WP_REST_Response for success responses and WP_Error for
     * error responses. Both are valid return values from REST callbacks.
     */
    public function toWp(): WP_REST_Response|WP_Error
    {
        if ($this->isError) {
            return new WP_Error(
                $this->errorCode,
                $this->errorMessage,
                array_merge($this->errorData, ['status' => $this->status]),
            );
        }

        $response = new WP_REST_Response($this->data, $this->status);

        foreach ($this->headers as $name => $value) {
            $response->header($name, $value);
        }

        return $response;
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------

    public function data(): mixed
    {
        return $this->data;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return array<string, mixed>
     */
    public function errorData(): array
    {
        return $this->errorData;
    }
}
