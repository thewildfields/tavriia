<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Taxonomy;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;
use TheWildFields\Tavriia\Taxonomy\TermFactory;
use TheWildFields\Tavriia\Tests\TestCase;

final class TermFactoryTest extends TestCase
{
    private TermFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new TermFactory();
    }

    private function makeDto(array $overrides = []): TaxonomyDTO
    {
        return new TaxonomyDTO(
            name: $overrides['name'] ?? 'Rock',
            taxonomy: $overrides['taxonomy'] ?? 'genre',
            slug: $overrides['slug'] ?? '',
            description: $overrides['description'] ?? '',
            parentId: $overrides['parentId'] ?? 0,
            meta: $overrides['meta'] ?? [],
        );
    }

    // --- create ---

    public function test_create_returns_term_id_on_success(): void
    {
        Functions\when('wp_insert_term')->justReturn(['term_id' => 33, 'term_taxonomy_id' => 33]);

        $id = $this->factory->create($this->makeDto());

        $this->assertSame(33, $id);
    }

    public function test_create_throws_when_wp_insert_term_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('term_exists');
        $wpError->shouldReceive('get_error_message')->andReturn('Term already exists.');

        Functions\when('wp_insert_term')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);
        $this->expectExceptionMessage('Failed to create term');

        $this->factory->create($this->makeDto());
    }

    public function test_create_throws_when_term_id_is_zero(): void
    {
        Functions\when('wp_insert_term')->justReturn(['term_id' => 0]);

        $this->expectException(TermNotFoundException::class);

        $this->factory->create($this->makeDto());
    }

    public function test_create_saves_meta_after_insertion(): void
    {
        Functions\when('wp_insert_term')->justReturn(['term_id' => 50, 'term_taxonomy_id' => 50]);

        Functions\expect('update_term_meta')
            ->once()
            ->with(50, 'featured', true)
            ->andReturn(true);

        $dto = $this->makeDto(['meta' => ['featured' => true]]);
        $id  = $this->factory->create($dto);

        $this->assertSame(50, $id);
    }

    public function test_create_does_not_save_meta_when_empty(): void
    {
        Functions\when('wp_insert_term')->justReturn(['term_id' => 5, 'term_taxonomy_id' => 5]);

        Functions\expect('update_term_meta')->never();

        $this->factory->create($this->makeDto(['meta' => []]));
    }

    // --- update ---

    public function test_update_returns_term_id_on_success(): void
    {
        Functions\when('wp_update_term')->justReturn(['term_id' => 20, 'term_taxonomy_id' => 20]);

        $id = $this->factory->update(20, $this->makeDto());

        $this->assertSame(20, $id);
    }

    public function test_update_throws_when_wp_update_term_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('db_error');
        $wpError->shouldReceive('get_error_message')->andReturn('Database error.');

        Functions\when('wp_update_term')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);
        $this->expectExceptionMessage('Failed to update term');

        $this->factory->update(20, $this->makeDto());
    }

    public function test_update_throws_when_term_id_is_zero(): void
    {
        Functions\when('wp_update_term')->justReturn(['term_id' => 0]);

        $this->expectException(TermNotFoundException::class);

        $this->factory->update(99, $this->makeDto());
    }

    public function test_update_saves_meta_after_update(): void
    {
        Functions\when('wp_update_term')->justReturn(['term_id' => 15, 'term_taxonomy_id' => 15]);

        Functions\expect('update_term_meta')
            ->once()
            ->with(15, 'icon', 'star')
            ->andReturn(true);

        $dto = $this->makeDto(['meta' => ['icon' => 'star']]);
        $this->factory->update(15, $dto);
    }

    // --- delete ---

    public function test_delete_succeeds_when_wp_delete_term_returns_true(): void
    {
        Functions\when('wp_delete_term')->justReturn(true);

        // Should not throw
        $this->factory->delete(10, 'genre');

        $this->assertTrue(true);
    }

    public function test_delete_throws_when_wp_delete_term_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('default_category');
        $wpError->shouldReceive('get_error_message')->andReturn('Cannot delete default category.');

        Functions\when('wp_delete_term')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);
        $this->expectExceptionMessage('Failed to delete term');

        $this->factory->delete(1, 'category');
    }

    public function test_delete_throws_when_wp_delete_term_returns_false(): void
    {
        Functions\when('wp_delete_term')->justReturn(false);

        $this->expectException(TermNotFoundException::class);

        $this->factory->delete(999, 'genre');
    }

    // --- setObjectTerms ---

    public function test_set_object_terms_succeeds_when_no_wp_error(): void
    {
        Functions\when('wp_set_object_terms')->justReturn([33]);

        // Should not throw
        $this->factory->setObjectTerms(1, 'genre', [33]);

        $this->assertTrue(true);
    }

    public function test_set_object_terms_throws_when_wp_set_object_terms_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('invalid_taxonomy');
        $wpError->shouldReceive('get_error_message')->andReturn('Invalid taxonomy.');

        Functions\when('wp_set_object_terms')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);
        $this->expectExceptionMessage('Failed to set terms');

        $this->factory->setObjectTerms(1, 'genre', [1, 2]);
    }

    // --- removeObjectTerms ---

    public function test_remove_object_terms_calls_set_object_terms_with_empty_array(): void
    {
        Functions\expect('wp_set_object_terms')
            ->once()
            ->with(5, [], 'genre', false)
            ->andReturn([]);

        $this->factory->removeObjectTerms(5, 'genre');
    }
}
