<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Query;

use TheWildFields\Tavriia\DTO\QueryArgsDTO;
use TheWildFields\Tavriia\Query\QueryBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class QueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new QueryBuilder();
    }

    // --- Defaults ---

    public function test_to_dto_returns_query_args_dto(): void
    {
        $dto = $this->builder->toDto();

        $this->assertInstanceOf(QueryArgsDTO::class, $dto);
    }

    public function test_default_post_type_is_post(): void
    {
        $dto = $this->builder->toDto();

        $this->assertSame('post', $dto->postType);
    }

    public function test_default_post_status_is_publish(): void
    {
        $dto = $this->builder->toDto();

        $this->assertSame('publish', $dto->postStatus);
    }

    public function test_default_posts_per_page_is_10(): void
    {
        $dto = $this->builder->toDto();

        $this->assertSame(10, $dto->postsPerPage);
    }

    // --- Immutability ---

    public function test_post_type_is_immutable(): void
    {
        $builder2 = $this->builder->postType('event');

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame('post', $this->builder->toDto()->postType);
        $this->assertSame('event', $builder2->toDto()->postType);
    }

    public function test_status_is_immutable(): void
    {
        $builder2 = $this->builder->status('draft');

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame('publish', $this->builder->toDto()->postStatus);
        $this->assertSame('draft', $builder2->toDto()->postStatus);
    }

    public function test_limit_is_immutable(): void
    {
        $builder2 = $this->builder->limit(5);

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame(10, $this->builder->toDto()->postsPerPage);
        $this->assertSame(5, $builder2->toDto()->postsPerPage);
    }

    public function test_page_is_immutable(): void
    {
        $builder2 = $this->builder->page(3);

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame(1, $this->builder->toDto()->paged);
        $this->assertSame(3, $builder2->toDto()->paged);
    }

    public function test_order_by_is_immutable(): void
    {
        $builder2 = $this->builder->orderBy('title', 'ASC');

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame('date', $this->builder->toDto()->orderBy);
        $this->assertSame('title', $builder2->toDto()->orderBy);
    }

    // --- postType ---

    public function test_post_type_sets_string(): void
    {
        $dto = $this->builder->postType('event')->toDto();

        $this->assertSame('event', $dto->postType);
    }

    public function test_post_type_sets_array(): void
    {
        $dto = $this->builder->postType(['post', 'page'])->toDto();

        $this->assertSame(['post', 'page'], $dto->postType);
    }

    // --- status ---

    public function test_status_sets_post_status(): void
    {
        $dto = $this->builder->status('any')->toDto();

        $this->assertSame('any', $dto->postStatus);
    }

    // --- limit ---

    public function test_limit_sets_posts_per_page(): void
    {
        $dto = $this->builder->limit(25)->toDto();

        $this->assertSame(25, $dto->postsPerPage);
    }

    // --- page ---

    public function test_page_sets_paged(): void
    {
        $dto = $this->builder->page(4)->toDto();

        $this->assertSame(4, $dto->paged);
    }

    public function test_page_minimum_is_one(): void
    {
        $dto = $this->builder->page(0)->toDto();

        $this->assertSame(1, $dto->paged);
    }

    public function test_page_negative_becomes_one(): void
    {
        $dto = $this->builder->page(-5)->toDto();

        $this->assertSame(1, $dto->paged);
    }

    // --- orderBy ---

    public function test_order_by_sets_order_by_and_order_desc(): void
    {
        $dto = $this->builder->orderBy('title')->toDto();

        $this->assertSame('title', $dto->orderBy);
        $this->assertSame('DESC', $dto->order);
    }

    public function test_order_by_sets_asc_order(): void
    {
        $dto = $this->builder->orderBy('title', 'ASC')->toDto();

        $this->assertSame('ASC', $dto->order);
    }

    public function test_order_by_normalizes_order_to_uppercase(): void
    {
        $dto = $this->builder->orderBy('date', 'asc')->toDto();

        $this->assertSame('ASC', $dto->order);
    }

    public function test_order_by_defaults_to_desc_for_invalid_values(): void
    {
        $dto = $this->builder->orderBy('date', 'random')->toDto();

        $this->assertSame('DESC', $dto->order);
    }

    // --- metaQuery ---

    public function test_meta_query_adds_clause(): void
    {
        $dto = $this->builder->metaQuery('color', 'red')->toDto();

        $this->assertCount(1, $dto->metaQuery);
        $this->assertSame('color', $dto->metaQuery[0]['key']);
        $this->assertSame('red', $dto->metaQuery[0]['value']);
        $this->assertSame('=', $dto->metaQuery[0]['compare']);
    }

    public function test_meta_query_accumulates_multiple_clauses(): void
    {
        $dto = $this->builder
            ->metaQuery('color', 'red')
            ->metaQuery('size', 'large')
            ->toDto();

        $this->assertCount(2, $dto->metaQuery);
    }

    public function test_meta_query_custom_compare_operator(): void
    {
        $dto = $this->builder->metaQuery('price', 100, '>')->toDto();

        $this->assertSame('>', $dto->metaQuery[0]['compare']);
    }

    // --- metaExists ---

    public function test_meta_exists_adds_exists_clause(): void
    {
        $dto = $this->builder->metaExists('thumbnail_id')->toDto();

        $this->assertCount(1, $dto->metaQuery);
        $this->assertSame('thumbnail_id', $dto->metaQuery[0]['key']);
        $this->assertSame('EXISTS', $dto->metaQuery[0]['compare']);
        $this->assertArrayNotHasKey('value', $dto->metaQuery[0]);
    }

    // --- taxQuery ---

    public function test_tax_query_adds_clause(): void
    {
        $dto = $this->builder->taxQuery('genre', [1, 2])->toDto();

        $this->assertCount(1, $dto->taxQuery);
        $this->assertSame('genre', $dto->taxQuery[0]['taxonomy']);
        $this->assertSame([1, 2], $dto->taxQuery[0]['terms']);
        $this->assertSame('term_id', $dto->taxQuery[0]['field']);
        $this->assertSame('IN', $dto->taxQuery[0]['operator']);
    }

    public function test_tax_query_accumulates_multiple_clauses(): void
    {
        $dto = $this->builder
            ->taxQuery('genre', [1])
            ->taxQuery('category', [5])
            ->toDto();

        $this->assertCount(2, $dto->taxQuery);
    }

    // --- whereIn ---

    public function test_where_in_sets_post_in(): void
    {
        $dto = $this->builder->whereIn([10, 20, 30])->toDto();

        $this->assertSame([10, 20, 30], $dto->postIn);
    }

    public function test_where_in_is_immutable(): void
    {
        $builder2 = $this->builder->whereIn([1, 2]);

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame([], $this->builder->toDto()->postIn);
    }

    // --- whereNotIn ---

    public function test_where_not_in_sets_post_not_in(): void
    {
        $dto = $this->builder->whereNotIn([5, 6])->toDto();

        $this->assertSame([5, 6], $dto->postNotIn);
    }

    // --- search ---

    public function test_search_sets_search_string(): void
    {
        $dto = $this->builder->search('hello world')->toDto();

        $this->assertSame('hello world', $dto->search);
    }

    public function test_search_is_immutable(): void
    {
        $builder2 = $this->builder->search('test');

        $this->assertNotSame($this->builder, $builder2);
        $this->assertSame('', $this->builder->toDto()->search);
    }

    // --- author ---

    public function test_author_sets_author_id(): void
    {
        $dto = $this->builder->author(7)->toDto();

        $this->assertSame(7, $dto->authorId);
    }

    // --- parent ---

    public function test_parent_sets_parent_id(): void
    {
        $dto = $this->builder->parent(3)->toDto();

        $this->assertSame(3, $dto->parentId);
    }

    // --- withArgs ---

    public function test_with_args_merges_extra_args(): void
    {
        $dto = $this->builder->withArgs(['no_found_rows' => true])->toDto();

        $this->assertTrue($dto->extra['no_found_rows']);
    }

    public function test_with_args_accumulates_on_multiple_calls(): void
    {
        $dto = $this->builder
            ->withArgs(['a' => 1])
            ->withArgs(['b' => 2])
            ->toDto();

        $this->assertSame(1, $dto->extra['a']);
        $this->assertSame(2, $dto->extra['b']);
    }
}
