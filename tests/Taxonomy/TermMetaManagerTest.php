<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Taxonomy;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Taxonomy\TermMetaManager;
use TheWildFields\Tavriia\Tests\TestCase;

final class TermMetaManagerTest extends TestCase
{
    private const TERM_ID = 77;

    private TermMetaManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new TermMetaManager(self::TERM_ID);
    }

    // --- getString via getRawValue ---

    public function test_get_string_returns_value_from_get_term_meta(): void
    {
        Functions\when('get_term_meta')->justReturn('genre-value');

        $result = $this->manager->getString('genre_key');

        $this->assertSame('genre-value', $result);
    }

    public function test_get_string_returns_default_when_get_term_meta_returns_empty_string(): void
    {
        Functions\when('get_term_meta')->justReturn('');

        $result = $this->manager->getString('missing_key', 'fallback');

        $this->assertSame('fallback', $result);
    }

    // --- getInt ---

    public function test_get_int_casts_string_to_integer(): void
    {
        Functions\when('get_term_meta')->justReturn('100');

        $result = $this->manager->getInt('count_key');

        $this->assertSame(100, $result);
    }

    public function test_get_int_returns_default_when_meta_empty(): void
    {
        Functions\when('get_term_meta')->justReturn('');

        $result = $this->manager->getInt('missing', 5);

        $this->assertSame(5, $result);
    }

    // --- getBool ---

    public function test_get_bool_returns_true_for_string_one(): void
    {
        Functions\when('get_term_meta')->justReturn('1');

        $this->assertTrue($this->manager->getBool('featured'));
    }

    public function test_get_bool_returns_false_for_string_zero(): void
    {
        Functions\when('get_term_meta')->justReturn('0');

        $this->assertFalse($this->manager->getBool('featured'));
    }

    // --- getArray ---

    public function test_get_array_returns_array_value(): void
    {
        Functions\when('get_term_meta')->justReturn(['a' => 1]);

        $result = $this->manager->getArray('data_key');

        $this->assertSame(['a' => 1], $result);
    }

    public function test_get_array_returns_default_when_meta_empty(): void
    {
        Functions\when('get_term_meta')->justReturn('');

        $result = $this->manager->getArray('missing', ['fallback']);

        $this->assertSame(['fallback'], $result);
    }

    // --- set ---

    public function test_set_calls_update_term_meta_with_correct_args(): void
    {
        Functions\expect('update_term_meta')
            ->once()
            ->with(self::TERM_ID, 'color', 'blue')
            ->andReturn(true);

        $result = $this->manager->set('color', 'blue');

        $this->assertTrue($result);
    }

    public function test_set_returns_false_when_update_term_meta_fails(): void
    {
        Functions\when('update_term_meta')->justReturn(false);

        $result = $this->manager->set('key', 'value');

        $this->assertFalse($result);
    }

    // --- delete ---

    public function test_delete_calls_delete_term_meta_with_key(): void
    {
        Functions\expect('delete_term_meta')
            ->once()
            ->with(self::TERM_ID, 'my_key', '')
            ->andReturn(true);

        $result = $this->manager->delete('my_key');

        $this->assertTrue($result);
    }

    public function test_delete_passes_value_when_provided(): void
    {
        Functions\expect('delete_term_meta')
            ->once()
            ->with(self::TERM_ID, 'key', 'specific')
            ->andReturn(true);

        $this->manager->delete('key', 'specific');
    }

    public function test_delete_returns_false_when_delete_term_meta_fails(): void
    {
        Functions\when('delete_term_meta')->justReturn(false);

        $result = $this->manager->delete('key');

        $this->assertFalse($result);
    }

    // --- has ---

    public function test_has_returns_true_when_term_meta_exists(): void
    {
        Functions\expect('metadata_exists')
            ->once()
            ->with('term', self::TERM_ID, 'my_key')
            ->andReturn(true);

        $this->assertTrue($this->manager->has('my_key'));
    }

    public function test_has_returns_false_when_term_meta_missing(): void
    {
        Functions\when('metadata_exists')->justReturn(false);

        $this->assertFalse($this->manager->has('missing'));
    }

    // --- all ---

    public function test_all_returns_flat_key_value_map(): void
    {
        Functions\when('get_term_meta')->justReturn([
            'label' => ['genre-label'],
            'order' => ['3'],
        ]);

        Functions\when('maybe_unserialize')->alias(fn($v) => $v);

        $result = $this->manager->all();

        $this->assertSame('genre-label', $result['label']);
        $this->assertSame('3', $result['order']);
    }

    public function test_all_returns_empty_array_when_no_term_meta(): void
    {
        Functions\when('get_term_meta')->justReturn([]);

        $result = $this->manager->all();

        $this->assertSame([], $result);
    }

    public function test_all_returns_empty_array_when_get_term_meta_returns_non_array(): void
    {
        Functions\when('get_term_meta')->justReturn(false);

        $result = $this->manager->all();

        $this->assertSame([], $result);
    }

    public function test_all_uses_first_value_from_meta_array(): void
    {
        Functions\when('get_term_meta')->justReturn([
            'item' => ['first', 'second'],
        ]);

        Functions\when('maybe_unserialize')->alias(fn($v) => $v);

        $result = $this->manager->all();

        $this->assertSame('first', $result['item']);
    }
}
