<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use Mockery;
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Tests\TestCase;

final class PostDTOTest extends TestCase
{
    public function test_construction_with_required_params_only(): void
    {
        $dto = new PostDTO(
            title: 'Hello World',
            content: 'Body text',
            status: 'publish',
            postType: 'post',
        );

        $this->assertSame('Hello World', $dto->title);
        $this->assertSame('Body text', $dto->content);
        $this->assertSame('publish', $dto->status);
        $this->assertSame('post', $dto->postType);
        $this->assertSame('', $dto->excerpt);
        $this->assertSame('', $dto->slug);
        $this->assertSame(0, $dto->authorId);
        $this->assertSame(0, $dto->parentId);
        $this->assertSame(0, $dto->menuOrder);
        $this->assertSame('', $dto->mimeType);
        $this->assertSame('', $dto->password);
        $this->assertSame('open', $dto->pingStatus);
        $this->assertSame('open', $dto->commentStatus);
        $this->assertNull($dto->dateGmt);
        $this->assertNull($dto->id);
        $this->assertSame([], $dto->meta);
        $this->assertSame([], $dto->termIds);
    }

    public function test_construction_with_all_params(): void
    {
        $dto = new PostDTO(
            title: 'Full Post',
            content: 'Content here',
            status: 'draft',
            postType: 'page',
            excerpt: 'Short excerpt',
            slug: 'full-post',
            authorId: 5,
            parentId: 10,
            menuOrder: 3,
            mimeType: 'image/jpeg',
            password: 'secret',
            pingStatus: 'closed',
            commentStatus: 'closed',
            dateGmt: '2025-01-15 12:00:00',
            id: 42,
            meta: ['key' => 'value'],
            termIds: [1, 2, 3],
        );

        $this->assertSame('Full Post', $dto->title);
        $this->assertSame('Content here', $dto->content);
        $this->assertSame('draft', $dto->status);
        $this->assertSame('page', $dto->postType);
        $this->assertSame('Short excerpt', $dto->excerpt);
        $this->assertSame('full-post', $dto->slug);
        $this->assertSame(5, $dto->authorId);
        $this->assertSame(10, $dto->parentId);
        $this->assertSame(3, $dto->menuOrder);
        $this->assertSame('image/jpeg', $dto->mimeType);
        $this->assertSame('secret', $dto->password);
        $this->assertSame('closed', $dto->pingStatus);
        $this->assertSame('closed', $dto->commentStatus);
        $this->assertSame('2025-01-15 12:00:00', $dto->dateGmt);
        $this->assertSame(42, $dto->id);
        $this->assertSame(['key' => 'value'], $dto->meta);
        $this->assertSame([1, 2, 3], $dto->termIds);
    }

    public function test_meta_default_is_empty_array(): void
    {
        $dto = new PostDTO('T', 'C', 'publish', 'post');

        $this->assertIsArray($dto->meta);
        $this->assertEmpty($dto->meta);
    }

    public function test_from_wp_post_maps_all_fields(): void
    {
        $wpPost = Mockery::mock('\WP_Post');
        $wpPost->ID             = 99;
        $wpPost->post_title     = 'WP Title';
        $wpPost->post_content   = 'WP Content';
        $wpPost->post_status    = 'publish';
        $wpPost->post_type      = 'event';
        $wpPost->post_excerpt   = 'WP Excerpt';
        $wpPost->post_name      = 'wp-title';
        $wpPost->post_author    = '7';
        $wpPost->post_parent    = '0';
        $wpPost->menu_order     = '2';
        $wpPost->post_mime_type = '';
        $wpPost->post_password  = '';
        $wpPost->ping_status    = 'open';
        $wpPost->comment_status = 'open';
        $wpPost->post_date_gmt  = '2025-03-01 10:00:00';

        $dto = PostDTO::fromWpPost($wpPost);

        $this->assertSame(99, $dto->id);
        $this->assertSame('WP Title', $dto->title);
        $this->assertSame('WP Content', $dto->content);
        $this->assertSame('publish', $dto->status);
        $this->assertSame('event', $dto->postType);
        $this->assertSame('WP Excerpt', $dto->excerpt);
        $this->assertSame('wp-title', $dto->slug);
        $this->assertSame(7, $dto->authorId);
        $this->assertSame(0, $dto->parentId);
        $this->assertSame(2, $dto->menuOrder);
        $this->assertSame('2025-03-01 10:00:00', $dto->dateGmt);
    }

    public function test_from_wp_post_sets_date_gmt_to_null_when_zero_date(): void
    {
        $wpPost = Mockery::mock('\WP_Post');
        $wpPost->ID             = 1;
        $wpPost->post_title     = 'T';
        $wpPost->post_content   = 'C';
        $wpPost->post_status    = 'publish';
        $wpPost->post_type      = 'post';
        $wpPost->post_excerpt   = '';
        $wpPost->post_name      = '';
        $wpPost->post_author    = '1';
        $wpPost->post_parent    = '0';
        $wpPost->menu_order     = '0';
        $wpPost->post_mime_type = '';
        $wpPost->post_password  = '';
        $wpPost->ping_status    = 'open';
        $wpPost->comment_status = 'open';
        $wpPost->post_date_gmt  = '0000-00-00 00:00:00';

        $dto = PostDTO::fromWpPost($wpPost);

        $this->assertNull($dto->dateGmt);
    }

    public function test_with_id_returns_new_instance_with_id_set(): void
    {
        $original = new PostDTO(
            title: 'Test',
            content: 'Body',
            status: 'publish',
            postType: 'post',
            meta: ['foo' => 'bar'],
            termIds: [5],
        );

        $withId = $original->withId(123);

        $this->assertNotSame($original, $withId);
        $this->assertNull($original->id);
        $this->assertSame(123, $withId->id);
        $this->assertSame('Test', $withId->title);
        $this->assertSame('Body', $withId->content);
        $this->assertSame(['foo' => 'bar'], $withId->meta);
        $this->assertSame([5], $withId->termIds);
    }

    public function test_with_id_preserves_all_other_fields(): void
    {
        $original = new PostDTO(
            title: 'Original',
            content: 'Content',
            status: 'draft',
            postType: 'page',
            excerpt: 'Excerpt',
            slug: 'original',
            authorId: 3,
            parentId: 7,
            menuOrder: 1,
            mimeType: '',
            password: 'pass',
            pingStatus: 'closed',
            commentStatus: 'closed',
            dateGmt: '2025-06-01 00:00:00',
        );

        $withId = $original->withId(50);

        $this->assertSame(50, $withId->id);
        $this->assertSame('Original', $withId->title);
        $this->assertSame('Content', $withId->content);
        $this->assertSame('draft', $withId->status);
        $this->assertSame('page', $withId->postType);
        $this->assertSame('Excerpt', $withId->excerpt);
        $this->assertSame('original', $withId->slug);
        $this->assertSame(3, $withId->authorId);
        $this->assertSame(7, $withId->parentId);
        $this->assertSame(1, $withId->menuOrder);
        $this->assertSame('pass', $withId->password);
        $this->assertSame('closed', $withId->pingStatus);
        $this->assertSame('closed', $withId->commentStatus);
        $this->assertSame('2025-06-01 00:00:00', $withId->dateGmt);
    }
}
