<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Query;

use TheWildFields\Tavriia\Contracts\QueryBuilderInterface;
use TheWildFields\Tavriia\DTO\QueryArgsDTO;

/**
 * Fluent builder that composes WP_Query arguments and returns a typed QueryResult.
 *
 * Every setter returns a new (cloned) instance so the builder is immutable.
 *
 * Usage:
 *   $result = $queryBuilder
 *       ->postType('event')
 *       ->status('publish')
 *       ->metaQuery('google_place_id', $placeId)
 *       ->orderBy('date', 'DESC')
 *       ->limit(10)
 *       ->page(2)
 *       ->get();
 */
final class QueryBuilder implements QueryBuilderInterface
{
    private string|array $postType = 'post';
    private string $postStatus = 'publish';
    private int $postsPerPage = 10;
    private int $paged = 1;
    private string $orderBy = 'date';
    private string $order = 'DESC';
    private int $authorId = 0;
    private int $parentId = -1;
    /** @var int[] */
    private array $postIn = [];
    /** @var int[] */
    private array $postNotIn = [];
    /** @var array<string, mixed>[] */
    private array $metaQueryClauses = [];
    /** @var array<string, mixed>[] */
    private array $taxQueryClauses = [];
    private string $search = '';
    /** @var array<string, mixed> */
    private array $extra = [];

    // -----------------------------------------------------------------
    // Fluent setters
    // -----------------------------------------------------------------

    /**
     * Set the post type (or multiple post types) to query.
     */
    public function postType(string|array $postType): static
    {
        $clone           = clone $this;
        $clone->postType = $postType;

        return $clone;
    }

    /**
     * Set the post status filter.
     */
    public function postStatus(string $status): static
    {
        $clone             = clone $this;
        $clone->postStatus = $status;

        return $clone;
    }

    /**
     * Add a meta query clause.
     *
     * Multiple calls accumulate clauses (relation defaults to AND).
     */
    public function metaQuery(string $key, mixed $value, string $compare = '='): static
    {
        $clone                      = clone $this;
        $clone->metaQueryClauses[]  = [
            'key'     => $key,
            'value'   => $value,
            'compare' => $compare,
        ];

        return $clone;
    }

    /**
     * Add a meta query clause that checks for key existence.
     */
    public function metaExists(string $key): static
    {
        $clone                     = clone $this;
        $clone->metaQueryClauses[] = [
            'key'     => $key,
            'compare' => 'EXISTS',
        ];

        return $clone;
    }

    /**
     * Add a taxonomy query clause.
     */
    public function taxQuery(
        string $taxonomy,
        array|int|string $terms,
        string $field = 'term_id',
        string $operator = 'IN',
    ): static {
        $clone                    = clone $this;
        $clone->taxQueryClauses[] = [
            'taxonomy' => $taxonomy,
            'field'    => $field,
            'terms'    => $terms,
            'operator' => $operator,
        ];

        return $clone;
    }

    /**
     * Set the field to order results by and the sort direction.
     */
    public function orderBy(string $orderBy, string $order = 'DESC'): static
    {
        $clone          = clone $this;
        $clone->orderBy = $orderBy;
        $clone->order   = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        return $clone;
    }

    /**
     * Set the maximum number of posts to return per page.
     */
    public function limit(int $limit): static
    {
        $clone               = clone $this;
        $clone->postsPerPage = $limit;

        return $clone;
    }

    /**
     * Set the page number for pagination.
     */
    public function page(int $page): static
    {
        $clone        = clone $this;
        $clone->paged = max(1, $page);

        return $clone;
    }

    /**
     * Filter by author ID.
     */
    public function author(int $authorId): static
    {
        $clone           = clone $this;
        $clone->authorId = $authorId;

        return $clone;
    }

    /**
     * Filter by parent post ID.
     */
    public function parent(int $parentId): static
    {
        $clone           = clone $this;
        $clone->parentId = $parentId;

        return $clone;
    }

    /**
     * Restrict results to specific post IDs.
     *
     * @param int[] $ids
     */
    public function whereIn(array $ids): static
    {
        $clone         = clone $this;
        $clone->postIn = $ids;

        return $clone;
    }

    /**
     * Exclude specific post IDs from results.
     *
     * @param int[] $ids
     */
    public function whereNotIn(array $ids): static
    {
        $clone            = clone $this;
        $clone->postNotIn = $ids;

        return $clone;
    }

    /**
     * Set a keyword search string.
     */
    public function search(string $query): static
    {
        $clone         = clone $this;
        $clone->search = $query;

        return $clone;
    }

    /**
     * Merge arbitrary extra WP_Query args not covered by named setters.
     *
     * @param array<string, mixed> $args
     */
    public function withArgs(array $args): static
    {
        $clone        = clone $this;
        $clone->extra = array_merge($clone->extra, $args);

        return $clone;
    }

    // -----------------------------------------------------------------
    // Build / execute
    // -----------------------------------------------------------------

    /**
     * Build and return the current state as a QueryArgsDTO.
     */
    public function toDto(): QueryArgsDTO
    {
        return new QueryArgsDTO(
            postType: $this->postType,
            postStatus: $this->postStatus,
            postsPerPage: $this->postsPerPage,
            paged: $this->paged,
            orderBy: $this->orderBy,
            order: $this->order,
            authorId: $this->authorId,
            parentId: $this->parentId,
            postIn: $this->postIn,
            postNotIn: $this->postNotIn,
            metaQuery: $this->metaQueryClauses,
            taxQuery: $this->taxQueryClauses,
            search: $this->search,
            extra: $this->extra,
        );
    }

    /**
     * Execute the query and return a typed QueryResult.
     */
    public function get(): QueryResult
    {
        $wpArgs = $this->toDto()->toWpQueryArgs();
        $query  = new \WP_Query($wpArgs);

        return QueryResult::fromWpQuery($query);
    }
}
