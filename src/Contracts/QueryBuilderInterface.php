<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\Query\QueryResult;

/**
 * Contract for a fluent WordPress query builder.
 */
interface QueryBuilderInterface
{
    /**
     * Set the post type to query.
     */
    public function postType(string $postType): static;

    /**
     * Add a meta query clause.
     */
    public function metaQuery(string $key, mixed $value, string $compare = '='): static;

    /**
     * Set the order-by field and direction.
     */
    public function orderBy(string $orderBy, string $order = 'DESC'): static;

    /**
     * Set the maximum number of posts to return.
     */
    public function limit(int $limit): static;

    /**
     * Execute the query and return a typed result set.
     */
    public function get(): QueryResult;
}
