<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Repository;

use TheWildFields\Tavriia\Contracts\RepositoryInterface;
use TheWildFields\Tavriia\Exceptions\EntityNotFoundException;

/**
 * Template-method base class for domain repositories.
 *
 * Consumers extend this class and implement loadById() / loadAll()
 * returning nulls and arrays of the source data. The base class takes
 * care of the throwing contract declared by RepositoryInterface, so
 * consumers never duplicate the "load-or-throw" boilerplate.
 *
 * The source of the data (WordPress DB, remote API, cache, composite)
 * is entirely up to the implementation.
 *
 * @template TEntity of object
 * @implements RepositoryInterface<TEntity>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Load a single entity by its identifier, or null if none exists.
     *
     * Implementations should not throw when the entity is missing —
     * return null and let the base class raise EntityNotFoundException.
     *
     * @return TEntity|null
     */
    abstract protected function loadById(int|string $id): ?object;

    /**
     * Load all entities matching the implementation's default criteria.
     *
     * @return list<TEntity>
     */
    abstract protected function loadAll(): array;

    /**
     * Human-readable entity name used in EntityNotFoundException messages.
     *
     * Defaults to the short class name with a trailing "Repository"
     * trimmed (e.g. "EventsRepository" → "Events"). Override to customise.
     */
    protected function entityName(): string
    {
        $short = (new \ReflectionClass(static::class))->getShortName();

        return preg_replace('/Repository$/', '', $short) ?: $short;
    }

    /**
     * @return TEntity
     *
     * @throws EntityNotFoundException
     */
    final public function findById(int|string $id): object
    {
        $entity = $this->loadById($id);

        if ($entity === null) {
            throw EntityNotFoundException::forIdentifier($this->entityName(), $id);
        }

        return $entity;
    }

    /**
     * @return TEntity|null
     */
    final public function findByIdOrNull(int|string $id): ?object
    {
        return $this->loadById($id);
    }

    /**
     * @return list<TEntity>
     */
    final public function all(): array
    {
        return $this->loadAll();
    }
}
