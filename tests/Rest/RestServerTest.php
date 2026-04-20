<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Rest;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;
use TheWildFields\Tavriia\Rest\RestServer;
use TheWildFields\Tavriia\Tests\TestCase;

final class RestServerTest extends TestCase
{
    private function makeDto(array $overrides = []): RestRouteDto
    {
        return new RestRouteDto(
            namespace:          $overrides['namespace']          ?? 'my-plugin/v1',
            route:              $overrides['route']              ?? '/events',
            methods:            $overrides['methods']            ?? 'GET',
            callback:           $overrides['callback']           ?? fn() => null,
            permissionCallback: $overrides['permissionCallback'] ?? fn() => true,
            args:               $overrides['args']               ?? [],
            schema:             $overrides['schema']             ?? null,
            override:           $overrides['override']           ?? false,
        );
    }

    // --- register ---

    public function test_register_calls_register_rest_route_once(): void
    {
        Functions\expect('register_rest_route')->once()->andReturn(true);

        (new RestServer())->register($this->makeDto());
    }

    public function test_register_succeeds_when_wp_returns_true(): void
    {
        Functions\when('register_rest_route')->justReturn(true);

        (new RestServer())->register($this->makeDto());

        // No exception thrown
        $this->assertTrue(true);
    }

    public function test_register_throws_when_wp_returns_false(): void
    {
        Functions\when('register_rest_route')->justReturn(false);

        $this->expectException(RestRouteRegistrationException::class);
        (new RestServer())->register($this->makeDto());
    }

    public function test_register_throws_on_empty_namespace(): void
    {
        // Should short-circuit before touching register_rest_route
        Functions\expect('register_rest_route')->never();

        $this->expectException(RestRouteRegistrationException::class);
        (new RestServer())->register($this->makeDto(['namespace' => '']));
    }

    public function test_register_throws_on_empty_route(): void
    {
        Functions\expect('register_rest_route')->never();

        $this->expectException(RestRouteRegistrationException::class);
        (new RestServer())->register($this->makeDto(['route' => '']));
    }

    public function test_register_exception_message_contains_namespace_and_route(): void
    {
        Functions\when('register_rest_route')->justReturn(false);

        try {
            (new RestServer())->register($this->makeDto([
                'namespace' => 'my-plugin/v2',
                'route'     => '/custom',
            ]));
            $this->fail('Expected RestRouteRegistrationException was not thrown.');
        } catch (RestRouteRegistrationException $e) {
            $this->assertStringContainsString('my-plugin/v2', $e->getMessage());
            $this->assertStringContainsString('/custom', $e->getMessage());
        }
    }

    // --- registerMany ---

    public function test_register_many_calls_register_rest_route_for_each_dto(): void
    {
        Functions\expect('register_rest_route')->times(3)->andReturn(true);

        (new RestServer())->registerMany([
            $this->makeDto(['route' => '/one']),
            $this->makeDto(['route' => '/two']),
            $this->makeDto(['route' => '/three']),
        ]);
    }

    public function test_register_many_accepts_empty_iterable(): void
    {
        Functions\expect('register_rest_route')->never();

        (new RestServer())->registerMany([]);

        $this->assertTrue(true);
    }

    public function test_register_many_accepts_generator(): void
    {
        Functions\expect('register_rest_route')->twice()->andReturn(true);

        $routes = (function () {
            yield $this->makeDto(['route' => '/a']);
            yield $this->makeDto(['route' => '/b']);
        })();

        (new RestServer())->registerMany($routes);
    }

    public function test_register_many_propagates_exceptions(): void
    {
        Functions\when('register_rest_route')->justReturn(false);

        $this->expectException(RestRouteRegistrationException::class);

        (new RestServer())->registerMany([
            $this->makeDto(['route' => '/broken']),
        ]);
    }
}
