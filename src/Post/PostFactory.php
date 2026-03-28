<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Post;

use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

/**
 * Typed wrapper around wp_insert_post() and wp_update_post().
 *
 * Factories return IDs. WP_Error is never returned — it is always
 * converted to a typed exception at the WordPress boundary.
 */
final class PostFactory
{
    /**
     * Create a new post and return its ID.
     *
     * @throws PostNotFoundException When WordPress reports an error inserting the post.
     */
    public function create(PostDTO $dto): int
    {
        $args = $this->dtoToArgs($dto);

        // wp_insert_post returns 0 or WP_Error on failure
        $result = wp_insert_post($args, true);

        if ($result instanceof \WP_Error) {
            throw new PostNotFoundException(sprintf(
                'Failed to create post of type "%s": [%s] %s',
                $dto->postType,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }

        $id = (int) $result;

        if ($id === 0) {
            throw new PostNotFoundException(sprintf(
                'Failed to create post of type "%s": wp_insert_post returned 0.',
                $dto->postType,
            ));
        }

        if ($dto->meta !== []) {
            $metaManager = new MetaManager($id);
            foreach ($dto->meta as $key => $value) {
                $metaManager->set((string) $key, $value);
            }
        }

        return $id;
    }

    /**
     * Update an existing post and return its ID.
     *
     * @throws PostNotFoundException When the post does not exist or the update fails.
     */
    public function update(int $id, PostDTO $dto): int
    {
        $args       = $this->dtoToArgs($dto);
        $args['ID'] = $id;

        $result = wp_update_post($args, true);

        if ($result instanceof \WP_Error) {
            throw new PostNotFoundException(sprintf(
                'Failed to update post %d: [%s] %s',
                $id,
                $result->get_error_code(),
                $result->get_error_message(),
            ));
        }

        $updatedId = (int) $result;

        if ($updatedId === 0) {
            throw PostNotFoundException::forId($id);
        }

        if ($dto->meta !== []) {
            $metaManager = new MetaManager($updatedId);
            foreach ($dto->meta as $key => $value) {
                $metaManager->set((string) $key, $value);
            }
        }

        return $updatedId;
    }

    /**
     * Permanently delete a post by ID.
     *
     * @throws PostNotFoundException When the post does not exist.
     */
    public function delete(int $id, bool $forceDelete = false): void
    {
        $post = get_post($id);

        if (!$post instanceof \WP_Post) {
            throw PostNotFoundException::forId($id);
        }

        $result = wp_delete_post($id, $forceDelete);

        if ($result === false || $result === null) {
            throw new PostNotFoundException(sprintf(
                'Failed to delete post %d.',
                $id,
            ));
        }
    }

    /**
     * Convert a PostDTO to a WP array suitable for wp_insert_post / wp_update_post.
     *
     * @return array<string, mixed>
     */
    private function dtoToArgs(PostDTO $dto): array
    {
        $args = [
            'post_title'     => $dto->title,
            'post_content'   => $dto->content,
            'post_status'    => $dto->status,
            'post_type'      => $dto->postType,
            'post_excerpt'   => $dto->excerpt,
            'post_name'      => $dto->slug,
            'post_mime_type' => $dto->mimeType,
            'post_password'  => $dto->password,
            'ping_status'    => $dto->pingStatus,
            'comment_status' => $dto->commentStatus,
            'menu_order'     => $dto->menuOrder,
        ];

        if ($dto->authorId > 0) {
            $args['post_author'] = $dto->authorId;
        }

        if ($dto->parentId > 0) {
            $args['post_parent'] = $dto->parentId;
        }

        if ($dto->dateGmt !== null) {
            $args['post_date_gmt'] = $dto->dateGmt;
        }

        return $args;
    }
}
