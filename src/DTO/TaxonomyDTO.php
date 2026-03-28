<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\DTO;

/**
 * Immutable data transfer object representing a WordPress taxonomy term.
 */
final readonly class TaxonomyDTO
{
    public function __construct(
        public string $name,
        public string $taxonomy,
        public string $slug = '',
        public string $description = '',
        public int $parentId = 0,
        public ?int $id = null,
        public int $count = 0,
        /** @var array<string, mixed> */
        public array $meta = [],
    ) {}

    /**
     * Create a TaxonomyDTO from a native \WP_Term object.
     */
    public static function fromWpTerm(\WP_Term $term): self
    {
        return new self(
            name: $term->name,
            taxonomy: $term->taxonomy,
            slug: $term->slug,
            description: $term->description,
            parentId: (int) $term->parent,
            id: (int) $term->term_id,
            count: (int) $term->count,
        );
    }

    /**
     * Return a new instance with the given ID set.
     */
    public function withId(int $id): self
    {
        return new self(
            name: $this->name,
            taxonomy: $this->taxonomy,
            slug: $this->slug,
            description: $this->description,
            parentId: $this->parentId,
            id: $id,
            count: $this->count,
            meta: $this->meta,
        );
    }
}
