<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\DTO;

/**
 * Immutable data transfer object representing a WordPress post.
 */
final readonly class PostDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $status,
        public string $postType,
        public string $excerpt = '',
        public string $slug = '',
        public int $authorId = 0,
        public int $parentId = 0,
        public int $menuOrder = 0,
        public string $mimeType = '',
        public string $password = '',
        public string $pingStatus = 'open',
        public string $commentStatus = 'open',
        public ?string $dateGmt = null,
        public ?int $id = null,
        /** @var array<string, mixed> */
        public array $meta = [],
        /** @var int[] */
        public array $termIds = [],
    ) {}

    /**
     * Create a PostDTO from a native \WP_Post object.
     */
    public static function fromWpPost(\WP_Post $post): self
    {
        return new self(
            title: $post->post_title,
            content: $post->post_content,
            status: $post->post_status,
            postType: $post->post_type,
            excerpt: $post->post_excerpt,
            slug: $post->post_name,
            authorId: (int) $post->post_author,
            parentId: (int) $post->post_parent,
            menuOrder: (int) $post->menu_order,
            mimeType: $post->post_mime_type,
            password: $post->post_password,
            pingStatus: $post->ping_status,
            commentStatus: $post->comment_status,
            dateGmt: $post->post_date_gmt !== '0000-00-00 00:00:00' ? $post->post_date_gmt : null,
            id: (int) $post->ID,
        );
    }

    /**
     * Return a new instance with the given ID set.
     */
    public function withId(int $id): self
    {
        return new self(
            title: $this->title,
            content: $this->content,
            status: $this->status,
            postType: $this->postType,
            excerpt: $this->excerpt,
            slug: $this->slug,
            authorId: $this->authorId,
            parentId: $this->parentId,
            menuOrder: $this->menuOrder,
            mimeType: $this->mimeType,
            password: $this->password,
            pingStatus: $this->pingStatus,
            commentStatus: $this->commentStatus,
            dateGmt: $this->dateGmt,
            id: $id,
            meta: $this->meta,
            termIds: $this->termIds,
        );
    }
}
