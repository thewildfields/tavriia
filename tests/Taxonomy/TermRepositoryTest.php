<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Taxonomy;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;
use TheWildFields\Tavriia\Taxonomy\TermMetaManager;
use TheWildFields\Tavriia\Taxonomy\TermRepository;
use TheWildFields\Tavriia\Tests\TestCase;

final class TermRepositoryTest extends TestCase
{
    private TermRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TermRepository();
    }

    private function makeWpTerm(int $id = 1, string $name = 'Rock'): object
    {
        $term = Mockery::mock('\WP_Term');
        $term->term_id     = $id;
        $term->name        = $name;
        $term->taxonomy    = 'genre';
        $term->slug        = strtolower($name);
        $term->description = '';
        $term->parent      = '0';
        $term->count       = '0';

        return $term;
    }

    // --- findById ---

    public function test_find_by_id_returns_taxonomy_dto(): void
    {
        $wpTerm = $this->makeWpTerm(5, 'Jazz');
        Functions\when('get_term')->justReturn($wpTerm);

        $dto = $this->repository->findById(5, 'genre');

        $this->assertInstanceOf(TaxonomyDTO::class, $dto);
        $this->assertSame(5, $dto->id);
        $this->assertSame('Jazz', $dto->name);
    }

    public function test_find_by_id_throws_when_get_term_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        Functions\when('get_term')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);

        $this->repository->findById(1, 'genre');
    }

    public function test_find_by_id_throws_when_get_term_returns_null(): void
    {
        Functions\when('get_term')->justReturn(null);

        $this->expectException(TermNotFoundException::class);

        $this->repository->findById(99, 'category');
    }

    public function test_find_by_id_throws_when_term_not_wp_term_instance(): void
    {
        Functions\when('get_term')->justReturn(false);

        $this->expectException(TermNotFoundException::class);

        $this->repository->findById(1, 'tag');
    }

    // --- findBy ---

    public function test_find_by_returns_taxonomy_dto_on_success(): void
    {
        $wpTerm = $this->makeWpTerm(10, 'Blues');
        Functions\when('get_term_by')->justReturn($wpTerm);

        $dto = $this->repository->findBy('slug', 'blues', 'genre');

        $this->assertInstanceOf(TaxonomyDTO::class, $dto);
        $this->assertSame('Blues', $dto->name);
    }

    public function test_find_by_throws_when_get_term_by_returns_false(): void
    {
        Functions\when('get_term_by')->justReturn(false);

        $this->expectException(TermNotFoundException::class);

        $this->repository->findBy('slug', 'nonexistent', 'genre');
    }

    public function test_find_by_throws_when_get_term_by_returns_non_wp_term(): void
    {
        Functions\when('get_term_by')->justReturn(null);

        $this->expectException(TermNotFoundException::class);

        $this->repository->findBy('name', 'Unknown', 'category');
    }

    // --- findAll ---

    public function test_find_all_returns_array_of_taxonomy_dtos(): void
    {
        $term1 = $this->makeWpTerm(1, 'Rock');
        $term2 = $this->makeWpTerm(2, 'Pop');
        Functions\when('get_terms')->justReturn([$term1, $term2]);

        $result = $this->repository->findAll('genre');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TaxonomyDTO::class, $result[0]);
        $this->assertSame('Rock', $result[0]->name);
        $this->assertSame('Pop', $result[1]->name);
    }

    public function test_find_all_returns_empty_array_when_no_terms(): void
    {
        Functions\when('get_terms')->justReturn([]);

        $result = $this->repository->findAll('genre');

        $this->assertSame([], $result);
    }

    public function test_find_all_returns_empty_array_when_get_terms_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        Functions\when('get_terms')->justReturn($wpError);

        $result = $this->repository->findAll('genre');

        $this->assertSame([], $result);
    }

    public function test_find_all_filters_non_wp_term_items(): void
    {
        $term = $this->makeWpTerm(1, 'Valid');
        Functions\when('get_terms')->justReturn([$term, 'not-a-term', null]);

        $result = $this->repository->findAll('genre');

        $this->assertCount(1, $result);
    }

    public function test_find_all_passes_default_args_to_get_terms(): void
    {
        Functions\expect('get_terms')
            ->once()
            ->with(Mockery::on(function (array $args): bool {
                return $args['taxonomy'] === 'genre'
                    && $args['hide_empty'] === false;
            }))
            ->andReturn([]);

        $this->repository->findAll('genre');
    }

    public function test_find_all_merges_custom_args(): void
    {
        Functions\expect('get_terms')
            ->once()
            ->with(Mockery::on(function (array $args): bool {
                return $args['taxonomy'] === 'genre'
                    && $args['number'] === 5;
            }))
            ->andReturn([]);

        $this->repository->findAll('genre', ['number' => 5]);
    }

    // --- findByObject ---

    public function test_find_by_object_returns_array_of_taxonomy_dtos(): void
    {
        $term = $this->makeWpTerm(3, 'Classical');
        Functions\when('wp_get_object_terms')->justReturn([$term]);

        $result = $this->repository->findByObject(100, 'genre');

        $this->assertCount(1, $result);
        $this->assertSame('Classical', $result[0]->name);
    }

    public function test_find_by_object_throws_when_wp_get_object_terms_returns_wp_error(): void
    {
        $wpError = Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_code')->andReturn('invalid_taxonomy');
        $wpError->shouldReceive('get_error_message')->andReturn('Invalid taxonomy.');

        Functions\when('wp_get_object_terms')->justReturn($wpError);

        $this->expectException(TermNotFoundException::class);
        $this->expectExceptionMessage('Failed to retrieve terms for object');

        $this->repository->findByObject(1, 'genre');
    }

    public function test_find_by_object_returns_empty_array_when_no_terms(): void
    {
        Functions\when('wp_get_object_terms')->justReturn([]);

        $result = $this->repository->findByObject(1, 'genre');

        $this->assertSame([], $result);
    }

    // --- exists ---

    public function test_exists_returns_true_when_term_found(): void
    {
        $wpTerm = $this->makeWpTerm(1);
        Functions\when('get_term')->justReturn($wpTerm);

        $this->assertTrue($this->repository->exists(1, 'genre'));
    }

    public function test_exists_returns_false_when_term_not_found(): void
    {
        Functions\when('get_term')->justReturn(null);

        $this->assertFalse($this->repository->exists(999, 'genre'));
    }

    // --- metaFor ---

    public function test_meta_for_returns_term_meta_manager(): void
    {
        $metaManager = $this->repository->metaFor(10);

        $this->assertInstanceOf(TermMetaManager::class, $metaManager);
    }
}
