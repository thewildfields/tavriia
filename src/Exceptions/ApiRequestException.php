<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

use RuntimeException;

use WP_Error;

/**
 * Thrown when an outbound HTTP request fails at the transport level (WP_Error).
 */
final class ApiRequestException extends RuntimeException
{
    public static function fromWpError(WP_Error $error): self
    {
        return new self(sprintf(
            'HTTP request failed: [%s] %s',
            $error->get_error_code(),
            $error->get_error_message(),
        ));
    }

    public static function forUrl(string $url, string $reason): self
    {
        return new self(sprintf('HTTP request to "%s" failed: %s', $url, $reason));
    }
}
