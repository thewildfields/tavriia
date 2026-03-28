<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\DTO\TaxonomyDTO;

/**
 * Contract for retrieving taxonomy terms from WordPress.
 */
interface TaxonomyRepositoryInterface
{
    /**
     * Find a term by its ID within a given taxonomy.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\TermNotFoundException When no term exists for the given ID.
     */
    public function findById(int $id, string $taxonomy): TaxonomyDTO;

    /**
     * Find a term by a given field and value within a taxonomy.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\TermNotFoundException When no matching term is found.
     */
    public function findBy(string $field, string|int $value, string $taxonomy): TaxonomyDTO;

    /**
     * Find all terms within a taxonomy, optionally filtered by query args.
     *
     * @return TaxonomyDTO[]
     */
    public function findAll(string $taxonomy, array $args = []): array;

    /**
     * Get the terms attached to a specific object (post) for a given taxonomy.
     *
     * @return TaxonomyDTO[]
     */
    public function findByObject(int $objectId, string $taxonomy): array;
}
