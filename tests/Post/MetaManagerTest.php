<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Post;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Post\MetaManager;
use TheWildFields\Tavriia\Tests\TestCase;

final class MetaManagerTest extends TestCase
{
    private const POST_ID = 42;

    private MetaManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new MetaManager(self::POST_ID);
    }

    // --- getString via getRawValue ---

    public function test_get_string_returns_value_from_get_post_meta(): void
    {
        Functions\when('get_post_meta')
            ->justReturn('my-value');

        $result = $this->manager->getString('my_key');

        $this->assertSame('my-value', $result);
    }

    public function test_get_string_returns_default_when_get_post_meta_returns_empty_string(): void
    {
        Functions\when('get_post_meta')->justReturn('');

        $result = $this->manager->getString('my_key', 'fallback');

        $this->assertSame('fallback', $result);
    }

    // --- getInt ---

    public function test_get_int_casts_string_to_integer(): void
    {
        Functions\when('get_post_meta')->justReturn('55');

        $result = $this->manager->getInt('count');

        $this->assertSame(55, $result);
    }

    public function test_get_int_returns_default_when_meta_not_found(): void
    {
        Functions\when('get_post_meta')->justReturn('');

        $result = $this->manager->getInt('missing', 10);

        $this->assertSame(10, $result);
    }

    // --- getBool ---

    public function test_get_bool_returns_true_for_value_one(): void
    {
        Functions\when('get_post_meta')->justReturn('1');

        $result = $this->manager->getBool('is_active');

        $this->assertTrue($result);
    }

    public function test_get_bool_returns_false_for_value_zero(): void
    {
        Functions\when('get_post_meta')->justReturn('0');

        $result = $this->manager->getBool('is_active');

        $this->assertFalse($result);
    }

    // --- getArray ---

    public function test_get_array_returns_array_value(): void
    {
        Functions\when('get_post_meta')->justReturn(['x' => 1]);

        $result = $this->manager->getArray('data');

        $this->assertSame(['x' => 1], $result);
    }

    public function test_get_array_returns_default_when_meta_not_found(): void
    {
        Functions\when('get_post_meta')->justReturn('');

        $result = $this->manager->getArray('missing', ['default']);

        $this->assertSame(['default'], $result);
    }

    // --- set ---

    public function test_set_calls_update_post_meta_with_correct_args(): void
    {
        Functions\expect('update_post_meta')
            ->once()
            ->with(self::POST_ID, 'my_key', 'my_value')
            ->andReturn(true);

        $result = $this->manager->set('my_key', 'my_value');

        $this->assertTrue($result);
    }

    public function test_set_returns_false_when_update_post_meta_returns_false(): void
    {
        Functions\when('update_post_meta')->justReturn(false);

        $result = $this->manager->set('key', 'value');

        $this->assertFalse($result);
    }

    // --- delete ---

    public function test_delete_calls_delete_post_meta_with_key(): void
    {
        Functions\expect('delete_post_meta')
            ->once()
            ->with(self::POST_ID, 'my_key', '')
            ->andReturn(true);

        $result = $this->manager->delete('my_key');

        $this->assertTrue($result);
    }

    public function test_delete_passes_value_when_provided(): void
    {
        Functions\expect('delete_post_meta')
            ->once()
            ->with(self::POST_ID, 'my_key', 'specific_value')
            ->andReturn(true);

        $this->manager->delete('my_key', 'specific_value');
    }

    public function test_delete_returns_false_when_delete_post_meta_fails(): void
    {
        Functions\when('delete_post_meta')->justReturn(false);

        $result = $this->manager->delete('key');

        $this->assertFalse($result);
    }

    // --- has ---

    public function test_has_returns_true_when_metadata_exists(): void
    {
        Functions\expect('metadata_exists')
            ->once()
            ->with('post', self::POST_ID, 'existing_key')
            ->andReturn(true);

        $result = $this->manager->has('existing_key');

        $this->assertTrue($result);
    }

    public function test_has_returns_false_when_metadata_does_not_exist(): void
    {
        Functions\when('metadata_exists')->justReturn(false);

        $result = $this->manager->has('missing_key');

        $this->assertFalse($result);
    }

    // --- all ---

    public function test_all_returns_flat_key_value_map(): void
    {
        Functions\when('get_post_meta')->justReturn([
            'name'  => ['Alice'],
            'age'   => ['30'],
        ]);

        Functions\when('maybe_unserialize')->alias(fn($v) => $v);

        $result = $this->manager->all();

        $this->assertSame('Alice', $result['name']);
        $this->assertSame('30', $result['age']);
    }

    public function test_all_returns_empty_array_when_no_meta(): void
    {
        Functions\when('get_post_meta')->justReturn([]);

        $result = $this->manager->all();

        $this->assertSame([], $result);
    }

    public function test_all_returns_empty_array_when_get_post_meta_returns_non_array(): void
    {
        Functions\when('get_post_meta')->justReturn(false);

        $result = $this->manager->all();

        $this->assertSame([], $result);
    }

    public function test_all_uses_first_value_from_each_meta_array(): void
    {
        Functions\when('get_post_meta')->justReturn([
            'multi' => ['first_value', 'second_value'],
        ]);

        Functions\when('maybe_unserialize')->alias(fn($v) => $v);

        $result = $this->manager->all();

        $this->assertSame('first_value', $result['multi']);
    }
}
