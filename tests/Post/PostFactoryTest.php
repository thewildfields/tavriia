<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Post;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Tests\TestCase;

final class PostFactoryTest extends TestCase
{
    private PostFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new PostFactory();
    }

    private function makeDto(array $overrides = []): PostDTO
    {
        return new PostDTO(
            title: $overrides['title'] ?? 'Test Post',
            content: $overrides['content'] ?? 'Test Content',
            status: $overrides['status'] ?? 'publish',
            postType: $overrides['postType'] ?? 'post',
            meta: $overrides['meta'] ?? [],
        );
    }

    // --- create ---

    public function test_create_returns_post_id_on_success(): void
    {
        Functions\when('wp_insert_post')->justReturn(42);

        $id = $this->factory->create($this->makeDto());

        $this->assertSame(42, $id);
    }

    public function test_create_throws_when_wp_insert_post_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('invalid_post');
        $wpError->shouldReceive('get_error_message')->andReturn('Post is invalid.');

        Functions\when('wp_insert_post')->justReturn($wpError);

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Failed to create post');

        $this->factory->create($this->makeDto());
    }

    public function test_create_throws_when_wp_insert_post_returns_zero(): void
    {
        Functions\when('wp_insert_post')->justReturn(0);

        $this->expectException(PostNotFoundException::class);

        $this->factory->create($this->makeDto());
    }

    public function test_create_saves_meta_after_insertion(): void
    {
        Functions\when('wp_insert_post')->justReturn(10);

        Functions\expect('update_post_meta')
            ->once()
            ->with(10, 'color', 'red')
            ->andReturn(true);

        $dto = $this->makeDto(['meta' => ['color' => 'red']]);
        $id  = $this->factory->create($dto);

        $this->assertSame(10, $id);
    }

    public function test_create_does_not_call_update_post_meta_when_meta_is_empty(): void
    {
        Functions\when('wp_insert_post')->justReturn(5);

        Functions\expect('update_post_meta')->never();

        $this->factory->create($this->makeDto(['meta' => []]));
    }

    // --- update ---

    public function test_update_returns_post_id_on_success(): void
    {
        Functions\when('wp_update_post')->justReturn(20);

        $id = $this->factory->update(20, $this->makeDto());

        $this->assertSame(20, $id);
    }

    public function test_update_throws_when_wp_update_post_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('db_error');
        $wpError->shouldReceive('get_error_message')->andReturn('Database error.');

        Functions\when('wp_update_post')->justReturn($wpError);

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Failed to update post');

        $this->factory->update(20, $this->makeDto());
    }

    public function test_update_throws_when_wp_update_post_returns_zero(): void
    {
        Functions\when('wp_update_post')->justReturn(0);

        $this->expectException(PostNotFoundException::class);

        $this->factory->update(99, $this->makeDto());
    }

    public function test_update_saves_meta_after_update(): void
    {
        Functions\when('wp_update_post')->justReturn(15);

        Functions\expect('update_post_meta')
            ->once()
            ->with(15, 'size', 'large')
            ->andReturn(true);

        $dto = $this->makeDto(['meta' => ['size' => 'large']]);
        $this->factory->update(15, $dto);
    }

    // --- delete ---

    public function test_delete_succeeds_when_post_exists_and_wp_delete_post_returns_post(): void
    {
        $wpPost = Mockery::mock('\WP_Post');

        Functions\when('get_post')->justReturn($wpPost);
        Functions\when('wp_delete_post')->justReturn($wpPost);

        // Should not throw
        $this->factory->delete(10);

        $this->assertTrue(true);
    }

    public function test_delete_throws_when_post_does_not_exist(): void
    {
        Functions\when('get_post')->justReturn(null);

        $this->expectException(PostNotFoundException::class);

        $this->factory->delete(999);
    }

    public function test_delete_throws_when_wp_delete_post_returns_false(): void
    {
        $wpPost = Mockery::mock('\WP_Post');

        Functions\when('get_post')->justReturn($wpPost);
        Functions\when('wp_delete_post')->justReturn(false);

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Failed to delete post');

        $this->factory->delete(10);
    }

    public function test_delete_throws_when_wp_delete_post_returns_null(): void
    {
        $wpPost = Mockery::mock('\WP_Post');

        Functions\when('get_post')->justReturn($wpPost);
        Functions\when('wp_delete_post')->justReturn(null);

        $this->expectException(PostNotFoundException::class);

        $this->factory->delete(10);
    }

    public function test_delete_passes_force_delete_flag(): void
    {
        $wpPost = Mockery::mock('\WP_Post');

        Functions\when('get_post')->justReturn($wpPost);

        Functions\expect('wp_delete_post')
            ->once()
            ->with(5, true)
            ->andReturn($wpPost);

        $this->factory->delete(5, true);
    }
}
