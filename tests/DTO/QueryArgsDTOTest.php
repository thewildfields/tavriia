<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use TheWildFields\Tavriia\DTO\QueryArgsDTO;
use TheWildFields\Tavriia\Tests\TestCase;

final class QueryArgsDTOTest extends TestCase
{
    public function test_construction_defaults(): void
    {
        $dto = new QueryArgsDTO();

        $this->assertSame('post', $dto->postType);
        $this->assertSame('publish', $dto->postStatus);
        $this->assertSame(10, $dto->postsPerPage);
        $this->assertSame(1, $dto->paged);
        $this->assertSame('date', $dto->orderBy);
        $this->assertSame('DESC', $dto->order);
        $this->assertSame(0, $dto->authorId);
        $this->assertSame(-1, $dto->parentId);
        $this->assertSame([], $dto->postIn);
        $this->assertSame([], $dto->postNotIn);
        $this->assertSame([], $dto->metaQuery);
        $this->assertSame([], $dto->taxQuery);
        $this->assertSame('', $dto->search);
        $this->assertSame([], $dto->extra);
    }

    public function test_to_wp_query_args_maps_basic_fields(): void
    {
        $dto = new QueryArgsDTO(
            postType: 'event',
            postStatus: 'draft',
            postsPerPage: 5,
            paged: 2,
            orderBy: 'title',
            order: 'ASC',
        );

        $args = $dto->toWpQueryArgs();

        $this->assertSame('event', $args['post_type']);
        $this->assertSame('draft', $args['post_status']);
        $this->assertSame(5, $args['posts_per_page']);
        $this->assertSame(2, $args['paged']);
        $this->assertSame('title', $args['orderby']);
        $this->assertSame('ASC', $args['order']);
    }

    public function test_to_wp_query_args_includes_author_when_greater_than_zero(): void
    {
        $dto  = new QueryArgsDTO(authorId: 5);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('author', $args);
        $this->assertSame(5, $args['author']);
    }

    public function test_to_wp_query_args_omits_author_when_zero(): void
    {
        $dto  = new QueryArgsDTO(authorId: 0);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('author', $args);
    }

    public function test_to_wp_query_args_includes_post_parent_when_zero_or_positive(): void
    {
        $dto  = new QueryArgsDTO(parentId: 0);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('post_parent', $args);
        $this->assertSame(0, $args['post_parent']);
    }

    public function test_to_wp_query_args_omits_post_parent_when_negative_one(): void
    {
        $dto  = new QueryArgsDTO(parentId: -1);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('post_parent', $args);
    }

    public function test_to_wp_query_args_includes_post_in_when_not_empty(): void
    {
        $dto  = new QueryArgsDTO(postIn: [10, 20, 30]);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('post__in', $args);
        $this->assertSame([10, 20, 30], $args['post__in']);
    }

    public function test_to_wp_query_args_omits_post_in_when_empty(): void
    {
        $dto  = new QueryArgsDTO(postIn: []);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('post__in', $args);
    }

    public function test_to_wp_query_args_includes_post_not_in_when_not_empty(): void
    {
        $dto  = new QueryArgsDTO(postNotIn: [5, 6]);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('post__not_in', $args);
        $this->assertSame([5, 6], $args['post__not_in']);
    }

    public function test_to_wp_query_args_omits_post_not_in_when_empty(): void
    {
        $dto  = new QueryArgsDTO(postNotIn: []);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('post__not_in', $args);
    }

    public function test_to_wp_query_args_includes_meta_query_when_not_empty(): void
    {
        $metaQuery = [['key' => 'color', 'value' => 'red', 'compare' => '=']];
        $dto       = new QueryArgsDTO(metaQuery: $metaQuery);
        $args      = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('meta_query', $args);
        $this->assertSame($metaQuery, $args['meta_query']);
    }

    public function test_to_wp_query_args_omits_meta_query_when_empty(): void
    {
        $dto  = new QueryArgsDTO(metaQuery: []);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('meta_query', $args);
    }

    public function test_to_wp_query_args_includes_tax_query_when_not_empty(): void
    {
        $taxQuery = [['taxonomy' => 'category', 'field' => 'slug', 'terms' => ['news']]];
        $dto      = new QueryArgsDTO(taxQuery: $taxQuery);
        $args     = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('tax_query', $args);
        $this->assertSame($taxQuery, $args['tax_query']);
    }

    public function test_to_wp_query_args_omits_tax_query_when_empty(): void
    {
        $dto  = new QueryArgsDTO(taxQuery: []);
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('tax_query', $args);
    }

    public function test_to_wp_query_args_includes_search_when_not_empty(): void
    {
        $dto  = new QueryArgsDTO(search: 'hello world');
        $args = $dto->toWpQueryArgs();

        $this->assertArrayHasKey('s', $args);
        $this->assertSame('hello world', $args['s']);
    }

    public function test_to_wp_query_args_omits_search_when_empty(): void
    {
        $dto  = new QueryArgsDTO(search: '');
        $args = $dto->toWpQueryArgs();

        $this->assertArrayNotHasKey('s', $args);
    }

    public function test_to_wp_query_args_merges_extra_args(): void
    {
        $dto  = new QueryArgsDTO(extra: ['ignore_sticky_posts' => true, 'no_found_rows' => true]);
        $args = $dto->toWpQueryArgs();

        $this->assertTrue($args['ignore_sticky_posts']);
        $this->assertTrue($args['no_found_rows']);
    }

    public function test_post_type_can_be_array(): void
    {
        $dto  = new QueryArgsDTO(postType: ['post', 'page']);
        $args = $dto->toWpQueryArgs();

        $this->assertSame(['post', 'page'], $args['post_type']);
    }
}
