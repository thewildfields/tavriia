<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

/**
 * Thrown when a requested WordPress taxonomy term does not exist.
 */
final class TermNotFoundException extends \RuntimeException
{
    public static function forId(int $id, string $taxonomy): self
    {
        return new self(sprintf(
            'Term with ID %d in taxonomy "%s" was not found.',
            $id,
            $taxonomy,
        ));
    }

    public static function forField(string $field, string|int $value, string $taxonomy): self
    {
        return new self(sprintf(
            'Term with %s "%s" in taxonomy "%s" was not found.',
            $field,
            (string) $value,
            $taxonomy,
        ));
    }
}
