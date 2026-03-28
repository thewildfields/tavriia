<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Exceptions;

use TheWildFields\Tavriia\Exceptions\PostNotFoundException;
use TheWildFields\Tavriia\Tests\TestCase;

final class PostNotFoundExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $e = PostNotFoundException::forId(1);

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_for_id_message_contains_id(): void
    {
        $e = PostNotFoundException::forId(42);

        $this->assertStringContainsString('42', $e->getMessage());
    }

    public function test_for_id_message_format(): void
    {
        $e = PostNotFoundException::forId(99);

        $this->assertSame('Post with ID 99 was not found.', $e->getMessage());
    }

    public function test_for_id_returns_post_not_found_exception(): void
    {
        $e = PostNotFoundException::forId(1);

        $this->assertInstanceOf(PostNotFoundException::class, $e);
    }

    public function test_direct_construction_with_message(): void
    {
        $e = new PostNotFoundException('Custom error message');

        $this->assertSame('Custom error message', $e->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $e);
    }
}
