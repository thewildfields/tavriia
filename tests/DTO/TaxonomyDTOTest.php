<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use Mockery;
use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Tests\TestCase;

final class TaxonomyDTOTest extends TestCase
{
    public function test_construction_with_required_params_only(): void
    {
        $dto = new TaxonomyDTO(
            name: 'Music',
            taxonomy: 'genre',
        );

        $this->assertSame('Music', $dto->name);
        $this->assertSame('genre', $dto->taxonomy);
        $this->assertSame('', $dto->slug);
        $this->assertSame('', $dto->description);
        $this->assertSame(0, $dto->parentId);
        $this->assertNull($dto->id);
        $this->assertSame(0, $dto->count);
        $this->assertSame([], $dto->meta);
    }

    public function test_construction_with_all_params(): void
    {
        $dto = new TaxonomyDTO(
            name: 'Rock',
            taxonomy: 'genre',
            slug: 'rock',
            description: 'Rock music',
            parentId: 2,
            id: 15,
            count: 42,
            meta: ['color' => 'red'],
        );

        $this->assertSame('Rock', $dto->name);
        $this->assertSame('genre', $dto->taxonomy);
        $this->assertSame('rock', $dto->slug);
        $this->assertSame('Rock music', $dto->description);
        $this->assertSame(2, $dto->parentId);
        $this->assertSame(15, $dto->id);
        $this->assertSame(42, $dto->count);
        $this->assertSame(['color' => 'red'], $dto->meta);
    }

    public function test_from_wp_term_maps_all_fields(): void
    {
        $wpTerm = Mockery::mock('\WP_Term');
        $wpTerm->term_id     = 77;
        $wpTerm->name        = 'Jazz';
        $wpTerm->taxonomy    = 'genre';
        $wpTerm->slug        = 'jazz';
        $wpTerm->description = 'Jazz music';
        $wpTerm->parent      = '3';
        $wpTerm->count       = '10';

        $dto = TaxonomyDTO::fromWpTerm($wpTerm);

        $this->assertSame(77, $dto->id);
        $this->assertSame('Jazz', $dto->name);
        $this->assertSame('genre', $dto->taxonomy);
        $this->assertSame('jazz', $dto->slug);
        $this->assertSame('Jazz music', $dto->description);
        $this->assertSame(3, $dto->parentId);
        $this->assertSame(10, $dto->count);
        $this->assertSame([], $dto->meta);
    }

    public function test_from_wp_term_casts_numeric_strings(): void
    {
        $wpTerm = Mockery::mock('\WP_Term');
        $wpTerm->term_id     = '55';
        $wpTerm->name        = 'Blues';
        $wpTerm->taxonomy    = 'genre';
        $wpTerm->slug        = 'blues';
        $wpTerm->description = '';
        $wpTerm->parent      = '0';
        $wpTerm->count       = '0';

        $dto = TaxonomyDTO::fromWpTerm($wpTerm);

        $this->assertSame(55, $dto->id);
        $this->assertSame(0, $dto->parentId);
        $this->assertSame(0, $dto->count);
    }

    public function test_with_id_returns_new_instance_with_id_set(): void
    {
        $original = new TaxonomyDTO(
            name: 'Pop',
            taxonomy: 'genre',
            slug: 'pop',
            meta: ['featured' => true],
        );

        $withId = $original->withId(99);

        $this->assertNotSame($original, $withId);
        $this->assertNull($original->id);
        $this->assertSame(99, $withId->id);
        $this->assertSame('Pop', $withId->name);
        $this->assertSame('genre', $withId->taxonomy);
        $this->assertSame('pop', $withId->slug);
        $this->assertSame(['featured' => true], $withId->meta);
    }

    public function test_with_id_preserves_all_other_fields(): void
    {
        $original = new TaxonomyDTO(
            name: 'Classical',
            taxonomy: 'genre',
            slug: 'classical',
            description: 'Classical music',
            parentId: 5,
            count: 20,
            meta: ['key' => 'val'],
        );

        $withId = $original->withId(88);

        $this->assertSame(88, $withId->id);
        $this->assertSame('Classical', $withId->name);
        $this->assertSame('genre', $withId->taxonomy);
        $this->assertSame('classical', $withId->slug);
        $this->assertSame('Classical music', $withId->description);
        $this->assertSame(5, $withId->parentId);
        $this->assertSame(20, $withId->count);
        $this->assertSame(['key' => 'val'], $withId->meta);
    }
}
