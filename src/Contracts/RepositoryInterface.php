<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\Exceptions\EntityNotFoundException;

/**
 * Generic contract for domain repositories.
 *
 * Implementations return typed DTOs and throw EntityNotFoundException
 * rather than returning null. The source of the data (WordPress DB,
 * remote API, cache, composite) is an implementation detail.
 *
 * @template TEntity of object
 */
interface RepositoryInterface
{
    /**
     * Find a single entity by its identifier.
     *
     * @return TEntity
     *
     * @throws EntityNotFoundException When no entity exists for the given identifier.
     */
    public function findById(int|string $id): object;

    /**
     * Find an entity by identifier, or null if it does not exist.
     *
     * Non-throwing counterpart to findById() for callers that need to
     * branch on existence without catching an exception.
     *
     * @return TEntity|null
     */
    public function findByIdOrNull(int|string $id): ?object;

    /**
     * Return all entities matching the implementation's default criteria.
     *
     * @return list<TEntity>
     */
    public function all(): array;
}
