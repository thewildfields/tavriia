<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

/**
 * Thrown when a requested WordPress post does not exist.
 */
final class PostNotFoundException extends \RuntimeException
{
    public static function forId(int $id): self
    {
        return new self(sprintf('Post with ID %d was not found.', $id));
    }
}
