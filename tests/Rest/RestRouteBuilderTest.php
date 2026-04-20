<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Rest;

use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;
use TheWildFields\Tavriia\Rest\RestRouteBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class RestRouteBuilderTest extends TestCase
{
    private function makeBuilder(): RestRouteBuilder
    {
        return (new RestRouteBuilder('my-plugin/v1', '/events'))
            ->callback(fn() => null)
            ->permissionCallback(fn() => true);
    }

    // --- build ---

    public function test_build_returns_rest_route_dto(): void
    {
        $dto = $this->makeBuilder()->build();

        $this->assertInstanceOf(RestRouteDto::class, $dto);
    }

    public function test_namespace_and_route_are_set_from_constructor(): void
    {
        $dto = $this->makeBuilder()->build();

        $this->assertSame('my-plugin/v1', $dto->namespace);
        $this->assertSame('/events', $dto->route);
    }

    public function test_default_method_is_get(): void
    {
        $dto = $this->makeBuilder()->build();

        $this->assertSame('GET', $dto->methods);
    }

    // --- Validation ---

    public function test_build_throws_when_namespace_is_empty(): void
    {
        $builder = (new RestRouteBuilder('', '/events'))
            ->callback(fn() => null)
            ->permissionCallback(fn() => true);

        $this->expectException(RestRouteRegistrationException::class);
        $builder->build();
    }

    public function test_build_throws_when_route_is_empty(): void
    {
        $builder = (new RestRouteBuilder('my-plugin/v1', ''))
            ->callback(fn() => null)
            ->permissionCallback(fn() => true);

        $this->expectException(RestRouteRegistrationException::class);
        $builder->build();
    }

    public function test_build_throws_when_callback_is_missing(): void
    {
        $builder = (new RestRouteBuilder('my-plugin/v1', '/events'))
            ->permissionCallback(fn() => true);

        $this->expectException(RestRouteRegistrationException::class);
        $builder->build();
    }

    public function test_build_throws_when_permission_callback_is_missing(): void
    {
        $builder = (new RestRouteBuilder('my-plugin/v1', '/events'))
            ->callback(fn() => null);

        $this->expectException(RestRouteRegistrationException::class);
        $builder->build();
    }

    // --- methods() ---

    public function test_methods_sets_method_string(): void
    {
        $dto = $this->makeBuilder()->methods('POST')->build();

        $this->assertSame('POST', $dto->methods);
    }

    public function test_methods_accepts_comma_separated_list(): void
    {
        $dto = $this->makeBuilder()->methods('GET, POST')->build();

        $this->assertSame('GET, POST', $dto->methods);
    }

    public function test_methods_is_immutable(): void
    {
        $builder = $this->makeBuilder();
        $other   = $builder->methods('DELETE');

        $this->assertNotSame($builder, $other);
        $this->assertSame('GET', $builder->build()->methods);
        $this->assertSame('DELETE', $other->build()->methods);
    }

    // --- HTTP verb shortcuts ---

    public function test_get_sets_method_and_callback(): void
    {
        $callback = fn() => 'hello';

        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->get($callback)
            ->permissionCallback(fn() => true)
            ->build();

        $this->assertSame('GET', $dto->methods);
        $this->assertSame($callback, $dto->callback);
    }

    public function test_post_sets_method_and_callback(): void
    {
        $callback = fn() => 'hello';

        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->post($callback)
            ->permissionCallback(fn() => true)
            ->build();

        $this->assertSame('POST', $dto->methods);
        $this->assertSame($callback, $dto->callback);
    }

    public function test_put_sets_method_and_callback(): void
    {
        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->put(fn() => null)
            ->permissionCallback(fn() => true)
            ->build();

        $this->assertSame('PUT', $dto->methods);
    }

    public function test_patch_sets_method_and_callback(): void
    {
        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->patch(fn() => null)
            ->permissionCallback(fn() => true)
            ->build();

        $this->assertSame('PATCH', $dto->methods);
    }

    public function test_delete_sets_method_and_callback(): void
    {
        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->delete(fn() => null)
            ->permissionCallback(fn() => true)
            ->build();

        $this->assertSame('DELETE', $dto->methods);
    }

    // --- permissionCallback / public ---

    public function test_permission_callback_is_stored(): void
    {
        $permission = fn() => true;

        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->callback(fn() => null)
            ->permissionCallback($permission)
            ->build();

        $this->assertSame($permission, $dto->permissionCallback);
    }

    public function test_public_shortcut_uses_return_true(): void
    {
        $dto = (new RestRouteBuilder('ns/v1', '/r'))
            ->callback(fn() => null)
            ->public()
            ->build();

        $this->assertSame('__return_true', $dto->permissionCallback);
    }

    // --- arg / args ---

    public function test_arg_adds_single_argument_definition(): void
    {
        $dto = $this->makeBuilder()
            ->arg('id', ['type' => 'integer', 'required' => true])
            ->build();

        $this->assertSame(
            ['id' => ['type' => 'integer', 'required' => true]],
            $dto->args,
        );
    }

    public function test_arg_can_be_called_multiple_times(): void
    {
        $dto = $this->makeBuilder()
            ->arg('id', ['type' => 'integer'])
            ->arg('slug', ['type' => 'string'])
            ->build();

        $this->assertArrayHasKey('id',   $dto->args);
        $this->assertArrayHasKey('slug', $dto->args);
    }

    public function test_args_replaces_whole_map(): void
    {
        $dto = $this->makeBuilder()
            ->arg('will-be-gone', ['type' => 'string'])
            ->args(['id' => ['type' => 'integer']])
            ->build();

        $this->assertSame(['id' => ['type' => 'integer']], $dto->args);
    }

    // --- schema ---

    public function test_schema_is_stored(): void
    {
        $schema = fn() => ['type' => 'object'];

        $dto = $this->makeBuilder()->schema($schema)->build();

        $this->assertSame($schema, $dto->schema);
    }

    // --- override ---

    public function test_override_defaults_to_false(): void
    {
        $dto = $this->makeBuilder()->build();

        $this->assertFalse($dto->override);
    }

    public function test_override_sets_flag_to_true(): void
    {
        $dto = $this->makeBuilder()->override()->build();

        $this->assertTrue($dto->override);
    }

    public function test_override_accepts_explicit_false(): void
    {
        $dto = $this->makeBuilder()->override(false)->build();

        $this->assertFalse($dto->override);
    }

    // --- Immutability ---

    public function test_arg_is_immutable(): void
    {
        $builder = $this->makeBuilder();
        $other   = $builder->arg('id', ['type' => 'integer']);

        $this->assertNotSame($builder, $other);
        $this->assertSame([], $builder->build()->args);
        $this->assertArrayHasKey('id', $other->build()->args);
    }

    public function test_public_is_immutable(): void
    {
        $base    = (new RestRouteBuilder('ns/v1', '/r'))->callback(fn() => null);
        $private = $base->permissionCallback(fn() => false);
        $public  = $base->public();

        $this->assertNotSame($private->build()->permissionCallback, $public->build()->permissionCallback);
    }
}
