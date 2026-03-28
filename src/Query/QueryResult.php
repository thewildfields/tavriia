<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Query;

use TheWildFields\Tavriia\DTO\PostDTO;

/**
 * Typed, immutable wrapper around WP_Query results.
 *
 * Provides array-like access to posts as PostDTOs along with
 * pagination metadata sourced from the underlying WP_Query instance.
 */
final class QueryResult implements \Countable, \IteratorAggregate
{
    /** @var PostDTO[] */
    private readonly array $posts;
    private readonly int $totalPosts;
    private readonly int $totalPages;
    private readonly int $currentPage;

    /**
     * @param PostDTO[] $posts
     */
    public function __construct(
        array $posts,
        int $totalPosts,
        int $totalPages,
        int $currentPage = 1,
    ) {
        $this->posts       = array_values($posts);
        $this->totalPosts  = $totalPosts;
        $this->totalPages  = $totalPages;
        $this->currentPage = $currentPage;
    }

    /**
     * Create a QueryResult from a WP_Query instance.
     */
    public static function fromWpQuery(\WP_Query $query): self
    {
        $posts = array_values(
            array_map(
                static fn(\WP_Post $post): PostDTO => PostDTO::fromWpPost($post),
                array_filter(
                    $query->posts ?? [],
                    static fn(mixed $p): bool => $p instanceof \WP_Post,
                ),
            ),
        );

        return new self(
            posts: $posts,
            totalPosts: (int) $query->found_posts,
            totalPages: (int) $query->max_num_pages,
            currentPage: (int) ($query->get('paged') ?: 1),
        );
    }

    /**
     * Return all posts in this result set.
     *
     * @return PostDTO[]
     */
    public function posts(): array
    {
        return $this->posts;
    }

    /**
     * Return the first post, or null when the result set is empty.
     */
    public function first(): ?PostDTO
    {
        return $this->posts[0] ?? null;
    }

    /**
     * Whether the result set contains no posts.
     */
    public function isEmpty(): bool
    {
        return $this->posts === [];
    }

    /**
     * Total number of posts matching the query (across all pages).
     */
    public function totalPosts(): int
    {
        return $this->totalPosts;
    }

    /**
     * Total number of pages available.
     */
    public function totalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Current page number.
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Whether there are more pages after the current one.
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    // -----------------------------------------------------------------
    // Countable
    // -----------------------------------------------------------------

    public function count(): int
    {
        return count($this->posts);
    }

    // -----------------------------------------------------------------
    // IteratorAggregate
    // -----------------------------------------------------------------

    /**
     * @return \ArrayIterator<int, PostDTO>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->posts);
    }
}
