<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Exceptions;

/**
 * Thrown when a domain repository cannot find an entity.
 *
 * Generic counterpart to PostNotFoundException / TermNotFoundException
 * for consumer repositories that load entities from the WordPress DB,
 * a remote API, or any combination of sources.
 */
final class EntityNotFoundException extends \RuntimeException
{
    public static function forIdentifier(string $entity, int|string $id): self
    {
        return new self(sprintf('%s with identifier "%s" was not found.', $entity, (string) $id));
    }
}
