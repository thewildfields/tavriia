<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Post;

use TheWildFields\Tavriia\Contracts\PostRepositoryInterface;
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\DTO\QueryArgsDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

/**
 * Typed wrapper around get_post(), get_posts(), and WP_Query.
 *
 * Always returns typed DTOs. Throws PostNotFoundException rather than
 * returning null or false. WP_Error never escapes this class.
 */
final class PostRepository implements PostRepositoryInterface
{
    /**
     * Find a single post by its ID.
     *
     * @throws PostNotFoundException When no post exists for the given ID.
     */
    public function findById(int $id): PostDTO
    {
        $post = get_post($id);

        if (!$post instanceof \WP_Post) {
            throw PostNotFoundException::forId($id);
        }

        return PostDTO::fromWpPost($post);
    }

    /**
     * Find multiple posts matching the given query arguments.
     *
     * @return PostDTO[]
     */
    public function findMany(QueryArgsDTO $args): array
    {
        $wpArgs          = $args->toWpQueryArgs();
        $wpArgs['fields'] = ''; // ensure we get WP_Post objects, not just IDs

        $posts = get_posts($wpArgs);

        if (!is_array($posts)) {
            return [];
        }

        return array_values(
            array_map(
                static fn(\WP_Post $post): PostDTO => PostDTO::fromWpPost($post),
                array_filter($posts, static fn(mixed $p): bool => $p instanceof \WP_Post),
            ),
        );
    }

    /**
     * Find the first post matching the given query arguments.
     *
     * @throws PostNotFoundException When no matching post is found.
     */
    public function findFirst(QueryArgsDTO $args): PostDTO
    {
        $limitedArgs = new QueryArgsDTO(
            postType: $args->postType,
            postStatus: $args->postStatus,
            postsPerPage: 1,
            paged: $args->paged,
            orderBy: $args->orderBy,
            order: $args->order,
            authorId: $args->authorId,
            parentId: $args->parentId,
            postIn: $args->postIn,
            postNotIn: $args->postNotIn,
            metaQuery: $args->metaQuery,
            taxQuery: $args->taxQuery,
            search: $args->search,
            extra: $args->extra,
        );

        $results = $this->findMany($limitedArgs);

        if ($results === []) {
            throw new PostNotFoundException(
                'No post found matching the given query arguments.',
            );
        }

        return $results[0];
    }

    /**
     * Check whether a post exists with the given ID.
     */
    public function exists(int $id): bool
    {
        return get_post($id) instanceof \WP_Post;
    }

    /**
     * Return a MetaManager bound to the given post ID without fetching the post.
     */
    public function metaFor(int $postId): MetaManager
    {
        return new MetaManager($postId);
    }
}
