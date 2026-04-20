<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Rest;

use Brain\Monkey\Functions;
use Mockery;
use TheWildFields\Tavriia\Contracts\HasHooksInterface;
use TheWildFields\Tavriia\Contracts\RestServerInterface;
use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Rest\AbstractRestController;
use TheWildFields\Tavriia\Tests\TestCase;

final class AbstractRestControllerTest extends TestCase
{
    private function makeRoute(string $route = '/events'): RestRouteDto
    {
        return new RestRouteDto(
            namespace: 'my-plugin/v1',
            route: $route,
            methods: 'GET',
            callback: fn() => null,
            permissionCallback: fn() => true,
        );
    }

    private function makeController(RestServerInterface $server, array $routes): AbstractRestController
    {
        return new class($server, $routes) extends AbstractRestController {
            public function __construct(
                RestServerInterface $server,
                /** @var RestRouteDto[] */
                private readonly array $routesToRegister,
            ) {
                parent::__construct($server);
            }

            protected function routes(): iterable
            {
                return $this->routesToRegister;
            }
        };
    }

    // --- Contract ---

    public function test_implements_has_hooks_interface(): void
    {
        $controller = $this->makeController(Mockery::mock(RestServerInterface::class), []);

        $this->assertInstanceOf(HasHooksInterface::class, $controller);
    }

    // --- register_hooks ---

    public function test_register_hooks_adds_rest_api_init_action(): void
    {
        // We stub add_action to capture the hook name directly; Brain Monkey's
        // expectAdded() tries to serialize the callback, which fails for
        // anonymous classes. Inspecting the arguments here is equivalent.
        $captured = [];
        Functions\when('add_action')->alias(
            function (string $hook, $callback) use (&$captured): void {
                $captured[] = $hook;
            },
        );

        $controller = $this->makeController(Mockery::mock(RestServerInterface::class), []);
        $controller->register_hooks();

        $this->assertContains('rest_api_init', $captured);
    }

    // --- registerRoutes ---

    public function test_register_routes_delegates_to_server(): void
    {
        $routes = [$this->makeRoute('/one'), $this->makeRoute('/two')];

        $server = Mockery::mock(RestServerInterface::class);
        $server->shouldReceive('registerMany')
            ->once()
            ->with(Mockery::on(function ($passed) use ($routes) {
                // routes() may return an array or a generator; normalize to array
                $collected = [];
                foreach ($passed as $item) {
                    $collected[] = $item;
                }

                return $collected === $routes;
            }));

        $controller = $this->makeController($server, $routes);
        $controller->registerRoutes();
    }

    public function test_register_routes_calls_server_with_empty_set(): void
    {
        $server = Mockery::mock(RestServerInterface::class);
        $server->shouldReceive('registerMany')->once();

        $controller = $this->makeController($server, []);
        $controller->registerRoutes();
    }

    // --- Generator support ---

    public function test_routes_can_be_provided_as_generator(): void
    {
        $routes = [$this->makeRoute('/a'), $this->makeRoute('/b')];

        $server = Mockery::mock(RestServerInterface::class);
        $server->shouldReceive('registerMany')
            ->once()
            ->with(Mockery::on(function ($passed) use ($routes) {
                $collected = [];
                foreach ($passed as $item) {
                    $collected[] = $item;
                }

                return count($collected) === count($routes);
            }));

        $controller = new class($server, $routes) extends AbstractRestController {
            public function __construct(
                RestServerInterface $server,
                /** @var RestRouteDto[] */
                private readonly array $routesToYield,
            ) {
                parent::__construct($server);
            }

            protected function routes(): iterable
            {
                foreach ($this->routesToYield as $route) {
                    yield $route;
                }
            }
        };

        $controller->registerRoutes();
    }
}
