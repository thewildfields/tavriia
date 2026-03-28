<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Post;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\DTO\QueryArgsDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;
use TheWildFields\Tavriia\Post\MetaManager;
use TheWildFields\Tavriia\Post\PostRepository;
use TheWildFields\Tavriia\Tests\TestCase;

final class PostRepositoryTest extends TestCase
{
    private PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PostRepository();
    }

    private function makeWpPost(int $id = 1, string $title = 'Test'): object
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID             = $id;
        $post->post_title     = $title;
        $post->post_content   = 'Content';
        $post->post_status    = 'publish';
        $post->post_type      = 'post';
        $post->post_excerpt   = '';
        $post->post_name      = 'test';
        $post->post_author    = '1';
        $post->post_parent    = '0';
        $post->menu_order     = '0';
        $post->post_mime_type = '';
        $post->post_password  = '';
        $post->ping_status    = 'open';
        $post->comment_status = 'open';
        $post->post_date_gmt  = '2025-01-01 12:00:00';

        return $post;
    }

    // --- findById ---

    public function test_find_by_id_returns_post_dto(): void
    {
        $wpPost = $this->makeWpPost(10, 'Hello');
        Functions\when('get_post')->justReturn($wpPost);

        $dto = $this->repository->findById(10);

        $this->assertInstanceOf(PostDTO::class, $dto);
        $this->assertSame(10, $dto->id);
        $this->assertSame('Hello', $dto->title);
    }

    public function test_find_by_id_throws_when_post_not_found(): void
    {
        Functions\when('get_post')->justReturn(null);

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('42');

        $this->repository->findById(42);
    }

    public function test_find_by_id_throws_when_get_post_returns_non_wp_post(): void
    {
        Functions\when('get_post')->justReturn(false);

        $this->expectException(PostNotFoundException::class);

        $this->repository->findById(1);
    }

    // --- findMany ---

    public function test_find_many_returns_array_of_post_dtos(): void
    {
        $wpPost1 = $this->makeWpPost(1, 'First');
        $wpPost2 = $this->makeWpPost(2, 'Second');

        Functions\when('get_posts')->justReturn([$wpPost1, $wpPost2]);

        $result = $this->repository->findMany(new QueryArgsDTO());

        $this->assertCount(2, $result);
        $this->assertInstanceOf(PostDTO::class, $result[0]);
        $this->assertInstanceOf(PostDTO::class, $result[1]);
        $this->assertSame('First', $result[0]->title);
        $this->assertSame('Second', $result[1]->title);
    }

    public function test_find_many_returns_empty_array_when_no_posts(): void
    {
        Functions\when('get_posts')->justReturn([]);

        $result = $this->repository->findMany(new QueryArgsDTO());

        $this->assertSame([], $result);
    }

    public function test_find_many_returns_empty_array_when_get_posts_returns_non_array(): void
    {
        Functions\when('get_posts')->justReturn(false);

        $result = $this->repository->findMany(new QueryArgsDTO());

        $this->assertSame([], $result);
    }

    public function test_find_many_filters_out_non_wp_post_items(): void
    {
        $wpPost = $this->makeWpPost(1, 'Valid');

        Functions\when('get_posts')->justReturn([$wpPost, 'not-a-post', null]);

        $result = $this->repository->findMany(new QueryArgsDTO());

        $this->assertCount(1, $result);
        $this->assertSame('Valid', $result[0]->title);
    }

    // --- findFirst ---

    public function test_find_first_returns_first_post(): void
    {
        $wpPost = $this->makeWpPost(5, 'First Result');
        Functions\when('get_posts')->justReturn([$wpPost]);

        $dto = $this->repository->findFirst(new QueryArgsDTO());

        $this->assertInstanceOf(PostDTO::class, $dto);
        $this->assertSame('First Result', $dto->title);
    }

    public function test_find_first_throws_when_no_results(): void
    {
        Functions\when('get_posts')->justReturn([]);

        $this->expectException(PostNotFoundException::class);

        $this->repository->findFirst(new QueryArgsDTO());
    }

    // --- exists ---

    public function test_exists_returns_true_when_post_found(): void
    {
        $wpPost = $this->makeWpPost(7);
        Functions\when('get_post')->justReturn($wpPost);

        $this->assertTrue($this->repository->exists(7));
    }

    public function test_exists_returns_false_when_post_not_found(): void
    {
        Functions\when('get_post')->justReturn(null);

        $this->assertFalse($this->repository->exists(999));
    }

    // --- metaFor ---

    public function test_meta_for_returns_meta_manager_instance(): void
    {
        $metaManager = $this->repository->metaFor(42);

        $this->assertInstanceOf(MetaManager::class, $metaManager);
    }
}
