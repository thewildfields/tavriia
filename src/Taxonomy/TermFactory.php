<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Taxonomy;

use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

/**
 * Typed wrapper around wp_insert_term(), wp_update_term(), wp_delete_term(),
 * and wp_set_object_terms().
 *
 * Factories return IDs. WP_Error is never returned — it is always
 * converted to a typed exception at the WordPress boundary.
 */
final class TermFactory
{
    /**
     * Create a new taxonomy term and return its term ID.
     *
     * @throws TermNotFoundException When WordPress reports an error inserting the term.
     */
    public function create(TaxonomyDTO $dto): int
    {
        $args = $this->dtoToArgs($dto);

        $result = wp_insert_term($dto->name, $dto->taxonomy, $args);

        if ($result instanceof \WP_Error) {
            throw new TermNotFoundException(sprintf(
                'Failed to create term "%s" in taxonomy "%s": [%s] %s',
                $dto->name,
                $dto->taxonomy,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }

        $termId = (int) ($result['term_id'] ?? 0);

        if ($termId === 0) {
            throw new TermNotFoundException(sprintf(
                'Failed to create term "%s" in taxonomy "%s": wp_insert_term returned no term_id.',
                $dto->name,
                $dto->taxonomy,
            ));
        }

        if ($dto->meta !== []) {
            $metaManager = new TermMetaManager($termId);
            foreach ($dto->meta as $key => $value) {
                $metaManager->set((string) $key, $value);
            }
        }

        return $termId;
    }

    /**
     * Update an existing taxonomy term and return its term ID.
     *
     * @throws TermNotFoundException When the term does not exist or the update fails.
     */
    public function update(int $id, TaxonomyDTO $dto): int
    {
        $args = $this->dtoToArgs($dto);

        if ($dto->name !== '') {
            $args['name'] = $dto->name;
        }

        $result = wp_update_term($id, $dto->taxonomy, $args);

        if ($result instanceof \WP_Error) {
            throw new TermNotFoundException(sprintf(
                'Failed to update term %d in taxonomy "%s": [%s] %s',
                $id,
                $dto->taxonomy,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }

        $termId = (int) ($result['term_id'] ?? 0);

        if ($termId === 0) {
            throw TermNotFoundException::forId($id, $dto->taxonomy);
        }

        if ($dto->meta !== []) {
            $metaManager = new TermMetaManager($termId);
            foreach ($dto->meta as $key => $value) {
                $metaManager->set((string) $key, $value);
            }
        }

        return $termId;
    }

    /**
     * Delete a taxonomy term by ID.
     *
     * @throws TermNotFoundException When the term does not exist or deletion fails.
     */
    public function delete(int $id, string $taxonomy): void
    {
        $result = wp_delete_term($id, $taxonomy);

        if ($result instanceof \WP_Error) {
            throw new TermNotFoundException(sprintf(
                'Failed to delete term %d from taxonomy "%s": [%s] %s',
                $id,
                $taxonomy,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }

        if ($result === false) {
            throw TermNotFoundException::forId($id, $taxonomy);
        }
    }

    /**
     * Assign a set of terms to an object (post) within a taxonomy.
     *
     * Replaces all existing terms on the object for that taxonomy.
     *
     * @param int[]|string[] $terms Term IDs or slugs to set.
     *
     * @throws TermNotFoundException When wp_set_object_terms() returns a WP_Error.
     */
    public function setObjectTerms(int $objectId, string $taxonomy, array $terms, bool $append = false): void
    {
        $result = wp_set_object_terms($objectId, $terms, $taxonomy, $append);

        if ($result instanceof \WP_Error) {
            throw new TermNotFoundException(sprintf(
                'Failed to set terms on object %d in taxonomy "%s": [%s] %s',
                $objectId,
                $taxonomy,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }
    }

    /**
     * Remove all terms from an object for a given taxonomy.
     *
     * @throws TermNotFoundException When wp_set_object_terms() returns a WP_Error.
     */
    public function removeObjectTerms(int $objectId, string $taxonomy): void
    {
        $this->setObjectTerms($objectId, $taxonomy, []);
    }

    /**
     * Convert a TaxonomyDTO to a WP args array for wp_insert_term / wp_update_term.
     *
     * @return array<string, mixed>
     */
    private function dtoToArgs(TaxonomyDTO $dto): array
    {
        $args = [];

        if ($dto->slug !== '') {
            $args['slug'] = $dto->slug;
        }

        if ($dto->description !== '') {
            $args['description'] = $dto->description;
        }

        if ($dto->parentId > 0) {
            $args['parent'] = $dto->parentId;
        }

        return $args;
    }
}
