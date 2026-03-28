<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\DTO\QueryArgsDTO;

/**
 * Contract for retrieving posts from WordPress.
 */
interface PostRepositoryInterface
{
    /**
     * Find a single post by its ID.
     *
     * @throws \TheWildFields\Tavriia\Exceptions\PostNotFoundException When no post exists for the given ID.
     */
    public function findById(int $id): PostDTO;

    /**
     * Find multiple posts matching the given query arguments.
     *
     * @return PostDTO[]
     */
    public function findMany(QueryArgsDTO $args): array;
}
