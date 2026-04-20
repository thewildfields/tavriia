<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Exceptions;

use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;
use TheWildFields\Tavriia\Tests\TestCase;

final class RestRouteRegistrationExceptionTest extends TestCase
{
    public function test_extends_runtime_exception(): void
    {
        $e = RestRouteRegistrationException::forRoute('my-plugin/v1', '/events');

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    public function test_for_route_message_contains_namespace(): void
    {
        $e = RestRouteRegistrationException::forRoute('my-plugin/v1', '/events');

        $this->assertStringContainsString('my-plugin/v1', $e->getMessage());
    }

    public function test_for_route_message_contains_route(): void
    {
        $e = RestRouteRegistrationException::forRoute('my-plugin/v1', '/events');

        $this->assertStringContainsString('/events', $e->getMessage());
    }

    public function test_for_missing_namespace_has_helpful_message(): void
    {
        $e = RestRouteRegistrationException::forMissingNamespace();

        $this->assertStringContainsString('namespace', $e->getMessage());
    }

    public function test_for_missing_route_has_helpful_message(): void
    {
        $e = RestRouteRegistrationException::forMissingRoute();

        $this->assertStringContainsString('route', $e->getMessage());
    }
}
