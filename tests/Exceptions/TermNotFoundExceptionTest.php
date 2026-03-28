<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Exceptions;

use TheWildFields\Tavriia\Exceptions\TermNotFoundException;
use TheWildFields\Tavriia\Tests\TestCase;

final class TermNotFoundExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $e = TermNotFoundException::forId(1, 'category');

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_for_id_message_contains_id(): void
    {
        $e = TermNotFoundException::forId(10, 'category');

        $this->assertStringContainsString('10', $e->getMessage());
    }

    public function test_for_id_message_contains_taxonomy(): void
    {
        $e = TermNotFoundException::forId(10, 'genre');

        $this->assertStringContainsString('genre', $e->getMessage());
    }

    public function test_for_id_message_format(): void
    {
        $e = TermNotFoundException::forId(5, 'post_tag');

        $this->assertSame('Term with ID 5 in taxonomy "post_tag" was not found.', $e->getMessage());
    }

    public function test_for_field_message_contains_field_name(): void
    {
        $e = TermNotFoundException::forField('slug', 'my-term', 'category');

        $this->assertStringContainsString('slug', $e->getMessage());
    }

    public function test_for_field_message_contains_field_value(): void
    {
        $e = TermNotFoundException::forField('slug', 'my-term', 'category');

        $this->assertStringContainsString('my-term', $e->getMessage());
    }

    public function test_for_field_message_contains_taxonomy(): void
    {
        $e = TermNotFoundException::forField('slug', 'my-term', 'custom_tax');

        $this->assertStringContainsString('custom_tax', $e->getMessage());
    }

    public function test_for_field_message_format_with_string_value(): void
    {
        $e = TermNotFoundException::forField('name', 'Rock', 'genre');

        $this->assertSame('Term with name "Rock" in taxonomy "genre" was not found.', $e->getMessage());
    }

    public function test_for_field_message_format_with_integer_value(): void
    {
        $e = TermNotFoundException::forField('id', 42, 'genre');

        $this->assertSame('Term with id "42" in taxonomy "genre" was not found.', $e->getMessage());
    }

    public function test_for_id_returns_term_not_found_exception(): void
    {
        $e = TermNotFoundException::forId(1, 'category');

        $this->assertInstanceOf(TermNotFoundException::class, $e);
    }

    public function test_for_field_returns_term_not_found_exception(): void
    {
        $e = TermNotFoundException::forField('slug', 'test', 'category');

        $this->assertInstanceOf(TermNotFoundException::class, $e);
    }
}
