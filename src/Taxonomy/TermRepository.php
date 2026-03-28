<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Taxonomy;

use TheWildFields\Tavriia\Contracts\TaxonomyRepositoryInterface;
use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

/**
 * Typed wrapper around get_terms(), get_term_by(), and wp_get_object_terms().
 *
 * Always returns typed DTOs. Throws TermNotFoundException rather than
 * returning null or WP_Error. WP_Error never escapes this class.
 */
final class TermRepository implements TaxonomyRepositoryInterface
{
    /**
     * Find a term by its ID within a given taxonomy.
     *
     * @throws TermNotFoundException When no term exists for the given ID.
     */
    public function findById(int $id, string $taxonomy): TaxonomyDTO
    {
        $term = get_term($id, $taxonomy);

        if ($term instanceof \WP_Error || !$term instanceof \WP_Term) {
            throw TermNotFoundException::forId($id, $taxonomy);
        }

        return TaxonomyDTO::fromWpTerm($term);
    }

    /**
     * Find a term by a given field and value within a taxonomy.
     *
     * Valid fields: 'id', 'name', 'slug', 'term_taxonomy_id'.
     *
     * @throws TermNotFoundException When no matching term is found.
     */
    public function findBy(string $field, string|int $value, string $taxonomy): TaxonomyDTO
    {
        $term = get_term_by($field, $value, $taxonomy);

        if ($term === false || !$term instanceof \WP_Term) {
            throw TermNotFoundException::forField($field, $value, $taxonomy);
        }

        return TaxonomyDTO::fromWpTerm($term);
    }

    /**
     * Find all terms within a taxonomy, optionally filtered by query args.
     *
     * @param array<string, mixed> $args  Optional get_terms() args (merged with defaults).
     * @return TaxonomyDTO[]
     */
    public function findAll(string $taxonomy, array $args = []): array
    {
        $defaultArgs = [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ];

        $terms = get_terms(array_merge($defaultArgs, $args));

        if ($terms instanceof \WP_Error || !is_array($terms)) {
            return [];
        }

        return array_values(
            array_map(
                static fn(\WP_Term $term): TaxonomyDTO => TaxonomyDTO::fromWpTerm($term),
                array_filter($terms, static fn(mixed $t): bool => $t instanceof \WP_Term),
            ),
        );
    }

    /**
     * Get the terms attached to a specific object (post) for a given taxonomy.
     *
     * @return TaxonomyDTO[]
     *
     * @throws TermNotFoundException When wp_get_object_terms() returns a WP_Error.
     */
    public function findByObject(int $objectId, string $taxonomy): array
    {
        $terms = wp_get_object_terms($objectId, $taxonomy);

        if ($terms instanceof \WP_Error) {
            throw new TermNotFoundException(sprintf(
                'Failed to retrieve terms for object %d in taxonomy "%s": [%s] %s',
                $objectId,
                $taxonomy,
                $terms->get_error_code(),
                $terms->get_error_message(),
            ));
        }

        if (!is_array($terms)) {
            return [];
        }

        return array_values(
            array_map(
                static fn(\WP_Term $term): TaxonomyDTO => TaxonomyDTO::fromWpTerm($term),
                array_filter($terms, static fn(mixed $t): bool => $t instanceof \WP_Term),
            ),
        );
    }

    /**
     * Check whether a term exists in the given taxonomy.
     */
    public function exists(int $id, string $taxonomy): bool
    {
        $term = get_term($id, $taxonomy);

        return $term instanceof \WP_Term;
    }

    /**
     * Return a TermMetaManager bound to the given term ID.
     */
    public function metaFor(int $termId): TermMetaManager
    {
        return new TermMetaManager($termId);
    }
}
