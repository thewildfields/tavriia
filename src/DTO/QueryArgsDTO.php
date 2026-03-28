<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\DTO;

/**
 * Immutable data transfer object representing WP_Query arguments.
 */
final readonly class QueryArgsDTO
{
    /**
     * @param string|string[]      $postType     Post type(s) to query.
     * @param string               $postStatus   Post status filter.
     * @param int                  $postsPerPage Number of posts to return (-1 = all).
     * @param int                  $paged        Current page number for pagination.
     * @param string               $orderBy      Field to order results by.
     * @param string               $order        Sort direction: 'ASC' or 'DESC'.
     * @param int                  $authorId     Filter by author ID (0 = any).
     * @param int                  $parentId     Filter by parent post ID (0 = any).
     * @param array<int>           $postIn       Limit to specific post IDs.
     * @param array<int>           $postNotIn    Exclude specific post IDs.
     * @param array<string, mixed>[] $metaQuery  Raw meta_query clauses.
     * @param array<string, mixed>[] $taxQuery   Raw tax_query clauses.
     * @param string               $search       Keyword search string.
     * @param array<string, mixed> $extra        Any extra WP_Query args.
     */
    public function __construct(
        public string|array $postType = 'post',
        public string $postStatus = 'publish',
        public int $postsPerPage = 10,
        public int $paged = 1,
        public string $orderBy = 'date',
        public string $order = 'DESC',
        public int $authorId = 0,
        public int $parentId = -1,
        /** @var int[] */
        public array $postIn = [],
        /** @var int[] */
        public array $postNotIn = [],
        /** @var array<string, mixed>[] */
        public array $metaQuery = [],
        /** @var array<string, mixed>[] */
        public array $taxQuery = [],
        public string $search = '',
        /** @var array<string, mixed> */
        public array $extra = [],
    ) {}

    /**
     * Convert to a raw WP_Query args array.
     *
     * @return array<string, mixed>
     */
    public function toWpQueryArgs(): array
    {
        $args = array_merge(
            $this->extra,
            [
                'post_type'      => $this->postType,
                'post_status'    => $this->postStatus,
                'posts_per_page' => $this->postsPerPage,
                'paged'          => $this->paged,
                'orderby'        => $this->orderBy,
                'order'          => $this->order,
            ],
        );

        if ($this->authorId > 0) {
            $args['author'] = $this->authorId;
        }

        if ($this->parentId >= 0) {
            $args['post_parent'] = $this->parentId;
        }

        if ($this->postIn !== []) {
            $args['post__in'] = $this->postIn;
        }

        if ($this->postNotIn !== []) {
            $args['post__not_in'] = $this->postNotIn;
        }

        if ($this->metaQuery !== []) {
            $args['meta_query'] = $this->metaQuery;
        }

        if ($this->taxQuery !== []) {
            $args['tax_query'] = $this->taxQuery;
        }

        if ($this->search !== '') {
            $args['s'] = $this->search;
        }

        return $args;
    }
}
