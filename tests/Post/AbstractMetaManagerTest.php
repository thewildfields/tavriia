<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Post;

use TheWildFields\Tavriia\Post\AbstractMetaManager;
use TheWildFields\Tavriia\Tests\TestCase;

final class AbstractMetaManagerTest extends TestCase
{
    /**
     * Create an anonymous concrete implementation with a controllable raw value.
     */
    private function makeManager(mixed $rawValue): AbstractMetaManager
    {
        return new class ($rawValue) extends AbstractMetaManager {
            public function __construct(private readonly mixed $rawValue)
            {
            }

            protected function getRawValue(string $key): mixed
            {
                return $this->rawValue;
            }
        };
    }

    // --- getString ---

    public function test_get_string_returns_string_value(): void
    {
        $manager = $this->makeManager('hello');

        $this->assertSame('hello', $manager->getString('any_key'));
    }

    public function test_get_string_returns_default_when_null(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame('default', $manager->getString('key', 'default'));
    }

    public function test_get_string_returns_default_when_false(): void
    {
        $manager = $this->makeManager(false);

        $this->assertSame('fallback', $manager->getString('key', 'fallback'));
    }

    public function test_get_string_returns_default_when_empty_string(): void
    {
        $manager = $this->makeManager('');

        $this->assertSame('default', $manager->getString('key', 'default'));
    }

    public function test_get_string_default_is_empty_string(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame('', $manager->getString('key'));
    }

    public function test_get_string_casts_integer_to_string(): void
    {
        $manager = $this->makeManager(42);

        $this->assertSame('42', $manager->getString('key'));
    }

    // --- getInt ---

    public function test_get_int_returns_integer(): void
    {
        $manager = $this->makeManager(99);

        $this->assertSame(99, $manager->getInt('key'));
    }

    public function test_get_int_casts_numeric_string_to_int(): void
    {
        $manager = $this->makeManager('123');

        $this->assertSame(123, $manager->getInt('key'));
    }

    public function test_get_int_returns_default_when_null(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame(7, $manager->getInt('key', 7));
    }

    public function test_get_int_returns_default_when_false(): void
    {
        $manager = $this->makeManager(false);

        $this->assertSame(0, $manager->getInt('key'));
    }

    public function test_get_int_default_is_zero(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame(0, $manager->getInt('key'));
    }

    // --- getFloat ---

    public function test_get_float_returns_float(): void
    {
        $manager = $this->makeManager(3.14);

        $this->assertSame(3.14, $manager->getFloat('key'));
    }

    public function test_get_float_casts_string_to_float(): void
    {
        $manager = $this->makeManager('2.718');

        $this->assertSame(2.718, $manager->getFloat('key'));
    }

    public function test_get_float_returns_default_when_null(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame(1.5, $manager->getFloat('key', 1.5));
    }

    public function test_get_float_default_is_zero(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame(0.0, $manager->getFloat('key'));
    }

    // --- getBool ---

    public function test_get_bool_returns_true_for_true(): void
    {
        $manager = $this->makeManager(true);

        $this->assertTrue($manager->getBool('key'));
    }

    public function test_get_bool_returns_false_for_false(): void
    {
        // false triggers default return, so test with explicit default
        $manager = $this->makeManager(null);

        $this->assertFalse($manager->getBool('key', false));
    }

    public function test_get_bool_returns_true_for_string_one(): void
    {
        $manager = $this->makeManager('1');

        $this->assertTrue($manager->getBool('key'));
    }

    public function test_get_bool_returns_true_for_string_true(): void
    {
        $manager = $this->makeManager('true');

        $this->assertTrue($manager->getBool('key'));
    }

    public function test_get_bool_returns_true_for_string_yes(): void
    {
        $manager = $this->makeManager('yes');

        $this->assertTrue($manager->getBool('key'));
    }

    public function test_get_bool_returns_true_for_string_on(): void
    {
        $manager = $this->makeManager('on');

        $this->assertTrue($manager->getBool('key'));
    }

    public function test_get_bool_returns_false_for_string_zero(): void
    {
        $manager = $this->makeManager('0');

        $this->assertFalse($manager->getBool('key'));
    }

    public function test_get_bool_returns_default_when_null(): void
    {
        $manager = $this->makeManager(null);

        $this->assertTrue($manager->getBool('key', true));
    }

    public function test_get_bool_default_is_false(): void
    {
        $manager = $this->makeManager(null);

        $this->assertFalse($manager->getBool('key'));
    }

    // --- getArray ---

    public function test_get_array_returns_array(): void
    {
        $manager = $this->makeManager(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $manager->getArray('key'));
    }

    public function test_get_array_returns_default_when_null(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame(['default'], $manager->getArray('key', ['default']));
    }

    public function test_get_array_returns_default_when_false(): void
    {
        $manager = $this->makeManager(false);

        $this->assertSame([], $manager->getArray('key'));
    }

    public function test_get_array_returns_default_when_not_array(): void
    {
        $manager = $this->makeManager('not-an-array');

        $this->assertSame([], $manager->getArray('key'));
    }

    public function test_get_array_default_is_empty_array(): void
    {
        $manager = $this->makeManager(null);

        $this->assertSame([], $manager->getArray('key'));
    }
}
