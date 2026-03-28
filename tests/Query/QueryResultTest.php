<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Query;

use Mockery;
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Query\QueryResult;
use TheWildFields\Tavriia\Tests\TestCase;

final class QueryResultTest extends TestCase
{
    private function makePostDto(int $id = 1, string $title = 'Test'): PostDTO
    {
        return new PostDTO(
            title: $title,
            content: 'Content',
            status: 'publish',
            postType: 'post',
            id: $id,
        );
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
        $post->post_date_gmt  = '0000-00-00 00:00:00';

        return $post;
    }

    // --- Direct construction ---

    public function test_construction_stores_posts(): void
    {
        $posts  = [$this->makePostDto(1), $this->makePostDto(2)];
        $result = new QueryResult($posts, 10, 2, 1);

        $this->assertCount(2, $result->posts());
    }

    public function test_construction_stores_total_posts(): void
    {
        $result = new QueryResult([], 42, 5, 1);

        $this->assertSame(42, $result->totalPosts());
    }

    public function test_construction_stores_total_pages(): void
    {
        $result = new QueryResult([], 50, 5, 1);

        $this->assertSame(5, $result->totalPages());
    }

    public function test_construction_stores_current_page(): void
    {
        $result = new QueryResult([], 0, 0, 3);

        $this->assertSame(3, $result->currentPage());
    }

    public function test_construction_default_current_page_is_one(): void
    {
        $result = new QueryResult([], 0, 0);

        $this->assertSame(1, $result->currentPage());
    }

    // --- fromWpQuery ---

    public function test_from_wp_query_maps_posts(): void
    {
        $wpPost = $this->makeWpPost(5, 'Hello');

        $query             = Mockery::mock('\WP_Query');
        $query->posts      = [$wpPost];
        $query->found_posts = 1;
        $query->max_num_pages = 1;
        $query->shouldReceive('get')->with('paged')->andReturn(1);

        $result = QueryResult::fromWpQuery($query);

        $this->assertCount(1, $result->posts());
        $this->assertSame('Hello', $result->posts()[0]->title);
    }

    public function test_from_wp_query_handles_empty_posts(): void
    {
        $query             = Mockery::mock('\WP_Query');
        $query->posts      = [];
        $query->found_posts = 0;
        $query->max_num_pages = 0;
        $query->shouldReceive('get')->with('paged')->andReturn(0);

        $result = QueryResult::fromWpQuery($query);

        $this->assertTrue($result->isEmpty());
    }

    public function test_from_wp_query_handles_null_posts(): void
    {
        $query             = Mockery::mock('\WP_Query');
        $query->posts      = null;
        $query->found_posts = 0;
        $query->max_num_pages = 0;
        $query->shouldReceive('get')->with('paged')->andReturn(0);

        $result = QueryResult::fromWpQuery($query);

        $this->assertTrue($result->isEmpty());
    }

    public function test_from_wp_query_filters_non_wp_post_items(): void
    {
        $wpPost = $this->makeWpPost(1);

        $query             = Mockery::mock('\WP_Query');
        $query->posts      = [$wpPost, 'not-a-post', null];
        $query->found_posts = 1;
        $query->max_num_pages = 1;
        $query->shouldReceive('get')->with('paged')->andReturn(1);

        $result = QueryResult::fromWpQuery($query);

        $this->assertCount(1, $result->posts());
    }

    public function test_from_wp_query_sets_current_page_to_one_when_paged_is_zero(): void
    {
        $query             = Mockery::mock('\WP_Query');
        $query->posts      = [];
        $query->found_posts = 0;
        $query->max_num_pages = 0;
        $query->shouldReceive('get')->with('paged')->andReturn(0);

        $result = QueryResult::fromWpQuery($query);

        $this->assertSame(1, $result->currentPage());
    }

    // --- posts() ---

    public function test_posts_returns_all_post_dtos(): void
    {
        $posts  = [$this->makePostDto(1, 'A'), $this->makePostDto(2, 'B')];
        $result = new QueryResult($posts, 2, 1);

        $all = $result->posts();

        $this->assertSame('A', $all[0]->title);
        $this->assertSame('B', $all[1]->title);
    }

    // --- first() ---

    public function test_first_returns_first_post(): void
    {
        $posts  = [$this->makePostDto(1, 'First'), $this->makePostDto(2, 'Second')];
        $result = new QueryResult($posts, 2, 1);

        $this->assertSame('First', $result->first()?->title);
    }

    public function test_first_returns_null_when_empty(): void
    {
        $result = new QueryResult([], 0, 0);

        $this->assertNull($result->first());
    }

    // --- isEmpty() ---

    public function test_is_empty_returns_true_when_no_posts(): void
    {
        $result = new QueryResult([], 0, 0);

        $this->assertTrue($result->isEmpty());
    }

    public function test_is_empty_returns_false_when_has_posts(): void
    {
        $result = new QueryResult([$this->makePostDto()], 1, 1);

        $this->assertFalse($result->isEmpty());
    }

    // --- hasNextPage() ---

    public function test_has_next_page_returns_true_when_more_pages_exist(): void
    {
        $result = new QueryResult([], 20, 3, 1);

        $this->assertTrue($result->hasNextPage());
    }

    public function test_has_next_page_returns_false_on_last_page(): void
    {
        $result = new QueryResult([], 20, 3, 3);

        $this->assertFalse($result->hasNextPage());
    }

    public function test_has_next_page_returns_false_when_single_page(): void
    {
        $result = new QueryResult([], 5, 1, 1);

        $this->assertFalse($result->hasNextPage());
    }

    // --- Countable ---

    public function test_count_returns_number_of_posts_in_result_set(): void
    {
        $posts  = [$this->makePostDto(1), $this->makePostDto(2), $this->makePostDto(3)];
        $result = new QueryResult($posts, 30, 10);

        $this->assertCount(3, $result);
        $this->assertSame(3, count($result));
    }

    public function test_count_returns_zero_when_empty(): void
    {
        $result = new QueryResult([], 0, 0);

        $this->assertSame(0, count($result));
    }

    // --- IteratorAggregate ---

    public function test_get_iterator_returns_array_iterator(): void
    {
        $posts  = [$this->makePostDto(1, 'A'), $this->makePostDto(2, 'B')];
        $result = new QueryResult($posts, 2, 1);

        $this->assertInstanceOf(\ArrayIterator::class, $result->getIterator());
    }

    public function test_result_is_iterable_with_foreach(): void
    {
        $posts  = [$this->makePostDto(1, 'A'), $this->makePostDto(2, 'B')];
        $result = new QueryResult($posts, 2, 1);

        $titles = [];
        foreach ($result as $post) {
            $titles[] = $post->title;
        }

        $this->assertSame(['A', 'B'], $titles);
    }

    // --- totalPosts / totalPages / currentPage ---

    public function test_total_posts_returns_total_count(): void
    {
        $result = new QueryResult([], 150, 15, 2);

        $this->assertSame(150, $result->totalPosts());
    }

    public function test_total_pages_returns_total_pages(): void
    {
        $result = new QueryResult([], 150, 15, 2);

        $this->assertSame(15, $result->totalPages());
    }

    public function test_current_page_returns_current_page(): void
    {
        $result = new QueryResult([], 150, 15, 7);

        $this->assertSame(7, $result->currentPage());
    }
}
