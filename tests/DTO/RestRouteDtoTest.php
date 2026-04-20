<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\DTO;

use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Tests\TestCase;

final class RestRouteDtoTest extends TestCase
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

    // --- Construction ---

    public function test_construction_with_required_params(): void
    {
        $callback   = fn() => null;
        $permission = fn() => true;

        $dto = new RestRouteDto(
            namespace: 'my-plugin/v1',
            route: '/events',
            methods: 'GET',
            callback: $callback,
            permissionCallback: $permission,
        );

        $this->assertSame('my-plugin/v1', $dto->namespace);
        $this->assertSame('/events', $dto->route);
        $this->assertSame('GET', $dto->methods);
        $this->assertSame($callback, $dto->callback);
        $this->assertSame($permission, $dto->permissionCallback);
        $this->assertSame([], $dto->args);
        $this->assertNull($dto->schema);
        $this->assertFalse($dto->override);
    }

    public function test_construction_with_all_params(): void
    {
        $callback   = fn() => null;
        $permission = fn() => true;
        $schema     = fn() => [];
        $args       = ['id' => ['type' => 'integer', 'required' => true]];

        $dto = new RestRouteDto(
            namespace: 'my-plugin/v1',
            route: '/events/(?P<id>\d+)',
            methods: 'GET, POST',
            callback: $callback,
            permissionCallback: $permission,
            args: $args,
            schema: $schema,
            override: true,
        );

        $this->assertSame('my-plugin/v1', $dto->namespace);
        $this->assertSame('/events/(?P<id>\d+)', $dto->route);
        $this->assertSame('GET, POST', $dto->methods);
        $this->assertSame($args, $dto->args);
        $this->assertSame($schema, $dto->schema);
        $this->assertTrue($dto->override);
    }

    // --- toWpArgs ---

    public function test_to_wp_args_returns_methods(): void
    {
        $args = $this->makeDto(['methods' => 'POST'])->toWpArgs();

        $this->assertSame('POST', $args['methods']);
    }

    public function test_to_wp_args_returns_callback(): void
    {
        $callback = fn() => 'hello';

        $args = $this->makeDto(['callback' => $callback])->toWpArgs();

        $this->assertSame($callback, $args['callback']);
    }

    public function test_to_wp_args_returns_permission_callback_under_snake_case_key(): void
    {
        $permission = fn() => true;

        $args = $this->makeDto(['permissionCallback' => $permission])->toWpArgs();

        $this->assertArrayHasKey('permission_callback', $args);
        $this->assertSame($permission, $args['permission_callback']);
        $this->assertArrayNotHasKey('permissionCallback', $args);
    }

    public function test_to_wp_args_returns_args(): void
    {
        $argDefs = ['id' => ['type' => 'integer', 'required' => true]];

        $args = $this->makeDto(['args' => $argDefs])->toWpArgs();

        $this->assertSame($argDefs, $args['args']);
    }

    public function test_to_wp_args_omits_schema_when_not_set(): void
    {
        $args = $this->makeDto()->toWpArgs();

        $this->assertArrayNotHasKey('schema', $args);
    }

    public function test_to_wp_args_includes_schema_when_set(): void
    {
        $schema = fn() => ['type' => 'object'];

        $args = $this->makeDto(['schema' => $schema])->toWpArgs();

        $this->assertArrayHasKey('schema', $args);
        $this->assertSame($schema, $args['schema']);
    }

    // --- Method constants ---

    public function test_method_constants_have_correct_values(): void
    {
        $this->assertSame('GET',    RestRouteDto::METHOD_GET);
        $this->assertSame('POST',   RestRouteDto::METHOD_POST);
        $this->assertSame('PUT',    RestRouteDto::METHOD_PUT);
        $this->assertSame('PATCH',  RestRouteDto::METHOD_PATCH);
        $this->assertSame('DELETE', RestRouteDto::METHOD_DELETE);
        $this->assertSame('GET, POST, PUT, PATCH, DELETE', RestRouteDto::METHODS_ALL);
    }
}
