---
title: REST API
description: Register WordPress REST API routes with typed route definitions and response objects
sidebar_position: 9
---

# REST API

Tavriia wraps WordPress' `register_rest_route()` with typed route definitions, a fluent route builder, a response helper, and a controller base class. Plugin code never touches `WP_Error` directly and never inspects boolean return values.

---

## Classes

| Class | Role |
|-------|------|
| `RestServer` | Registers routes with WordPress (wraps `register_rest_route()`) |
| `RestRouteBuilder` | Fluent builder producing `RestRouteDto` instances |
| `RestRouteDto` | Immutable route definition |
| `RestResponse` | Typed response object for REST callbacks |
| `AbstractRestController` | Base class for grouping related endpoints |
| `RestRouteRegistrationException` | Thrown when route registration fails |

---

## Quick Start

### Register a route

```php
use TheWildFields\Tavriia\Rest\RestRouteBuilder;
use TheWildFields\Tavriia\Rest\RestServer;

$server = new RestServer();

add_action('rest_api_init', function () use ($server) {
    $route = (new RestRouteBuilder('my-plugin/v1', '/events'))
        ->get([$this, 'listEvents'])
        ->public()
        ->build();

    $server->register($route);
});
```

### Return typed responses

```php
use TheWildFields\Tavriia\Rest\RestResponse;

public function listEvents(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
{
    $events = $this->repository->findMany($args);

    return RestResponse::ok($events)->toWp();
}
```

---

## RestRouteBuilder

`RestRouteBuilder` is a fluent, immutable builder. Every method returns a new instance, so you can reuse partial configurations without worrying about shared state.

### Constructor

```php
new RestRouteBuilder(
    namespace: 'my-plugin/v1',
    route:     '/events/(?P<id>\d+)',
);
```

### HTTP verb shortcuts

```php
$route = (new RestRouteBuilder('my-plugin/v1', '/events'))
    ->get([$this, 'list'])          // GET
    ->public()
    ->build();

$route = (new RestRouteBuilder('my-plugin/v1', '/events'))
    ->post([$this, 'create'])       // POST
    ->permissionCallback([$this, 'canCreate'])
    ->build();
```

Available shortcuts: `get()`, `post()`, `put()`, `patch()`, `delete()`.

### Permission callbacks

Every route needs a permission callback. Use `public()` for anonymous access (`__return_true`), or pass your own callable to `permissionCallback()`.

```php
// Public
$builder->public();

// Role check
$builder->permissionCallback(fn() => current_user_can('manage_options'));

// Method on a controller
$builder->permissionCallback([$this, 'canAccess']);
```

### Argument schemas

Declare validated URL and body arguments:

```php
$route = (new RestRouteBuilder('my-plugin/v1', '/events/(?P<id>\d+)'))
    ->get([$this, 'show'])
    ->public()
    ->arg('id', [
        'type'              => 'integer',
        'required'          => true,
        'sanitize_callback' => 'absint',
    ])
    ->arg('expand', [
        'type'    => 'string',
        'default' => 'none',
    ])
    ->build();
```

Or replace the whole map at once with `args()`:

```php
$builder->args([
    'id'     => ['type' => 'integer', 'required' => true],
    'expand' => ['type' => 'string', 'default' => 'none'],
]);
```

### Immutability

```php
$base = (new RestRouteBuilder('my-plugin/v1', '/events'))
    ->public();

$listRoute   = $base->get([$this, 'list'])->build();
$createRoute = $base->post([$this, 'create'])->build();
```

`$base` is never mutated — both derived builders reuse its namespace, route, and permission callback without interfering with each other.

---

## RestServer

`RestServer` owns the call to `register_rest_route()`. It throws `RestRouteRegistrationException` on:

- Empty namespace
- Empty route pattern
- WordPress reporting `false` from `register_rest_route()`

```php
use TheWildFields\Tavriia\Rest\RestServer;
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;

try {
    $server->register($route);
} catch (RestRouteRegistrationException $e) {
    // log, report, fail loudly — no silent false-returns
}
```

### Registering multiple routes at once

```php
$server->registerMany([
    $listRoute,
    $createRoute,
    $showRoute,
]);
```

`registerMany()` accepts any iterable, including generators — handy for controllers that `yield` routes lazily.

---

## RestResponse

`RestResponse` is a typed value object returned from REST callbacks. Call `toWp()` at the WordPress boundary to convert it to the native `WP_REST_Response` or `WP_Error`.

### Success responses

```php
use TheWildFields\Tavriia\Rest\RestResponse;

RestResponse::ok(['id' => 1, 'title' => 'Summer Concert']);     // 200
RestResponse::created($newEvent);                                // 201
RestResponse::noContent();                                       // 204
```

Add response headers:

```php
RestResponse::ok($events, ['X-Total-Count' => (string) $total])->toWp();
```

### Error responses

```php
RestResponse::badRequest('invalid_title', 'Title is required.');         // 400
RestResponse::unauthorized('no_auth', 'Authentication required.');       // 401
RestResponse::forbidden('no_perm', 'You cannot edit this event.');       // 403
RestResponse::notFound('event_not_found', 'No event with that ID.');     // 404
RestResponse::serverError('sync_failed', 'Upstream sync failed.');       // 500
```

Custom statuses and extra error data:

```php
RestResponse::error(
    code: 'unprocessable',
    message: 'Validation failed.',
    status: 422,
    data: ['fields' => ['start_date', 'venue_id']],
);
```

### Inside a callback

```php
public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
{
    try {
        $event = $this->repository->findById((int) $request['id']);

        return RestResponse::ok($event)->toWp();
    } catch (PostNotFoundException $e) {
        return RestResponse::notFound('event_not_found', $e->getMessage())->toWp();
    }
}
```

---

## AbstractRestController

For plugins with more than a couple of routes, extend `AbstractRestController`. Declare routes in the `routes()` method; the framework wires them into `rest_api_init` for you.

```php
use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Rest\AbstractRestController;
use TheWildFields\Tavriia\Rest\RestRouteBuilder;
use TheWildFields\Tavriia\Rest\RestResponse;
use TheWildFields\Tavriia\Rest\RestServer;

final class EventsController extends AbstractRestController
{
    public function __construct(
        RestServer $server,
        private readonly EventRepository $repository,
    ) {
        parent::__construct($server);
    }

    protected function routes(): iterable
    {
        yield (new RestRouteBuilder('my-plugin/v1', '/events'))
            ->get([$this, 'list'])
            ->public()
            ->build();

        yield (new RestRouteBuilder('my-plugin/v1', '/events/(?P<id>\d+)'))
            ->get([$this, 'show'])
            ->public()
            ->arg('id', ['type' => 'integer', 'required' => true])
            ->build();

        yield (new RestRouteBuilder('my-plugin/v1', '/events'))
            ->post([$this, 'create'])
            ->permissionCallback([$this, 'canCreate'])
            ->build();
    }

    public function list(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        return RestResponse::ok($this->repository->all())->toWp();
    }

    public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        try {
            $event = $this->repository->findById((int) $request['id']);
            return RestResponse::ok($event)->toWp();
        } catch (PostNotFoundException $e) {
            return RestResponse::notFound('event_not_found', $e->getMessage())->toWp();
        }
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $this->repository->create($request->get_json_params());

        return RestResponse::created(['id' => $id])->toWp();
    }

    public function canCreate(): bool
    {
        return current_user_can('edit_posts');
    }
}
```

Because `AbstractRestController` implements `HasHooksInterface`, it plugs directly into your plugin's module boot sequence:

```php
// In your plugin bootstrap:
$controller = new EventsController($server, $eventRepository);
$controller->register_hooks(); // registers the rest_api_init action
```

---

## Error Handling

`RestRouteRegistrationException` is thrown by both `RestRouteBuilder::build()` and `RestServer::register()`. It extends `\RuntimeException`, so you can either let it bubble up during bootstrap or catch it where route wiring happens.

```php
use TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException;

try {
    $server->register($route);
} catch (RestRouteRegistrationException $e) {
    error_log('[my-plugin] REST route registration failed: ' . $e->getMessage());
}
```

Common causes:

- Empty namespace or route pattern
- Missing callback in the builder
- Missing permission callback in the builder
- Invalid route regex (WordPress rejects it internally)

---

## API Reference

- [`RestServer`](api-reference/rest-server.md)
- [`RestRouteBuilder`](api-reference/rest-route-builder.md)
- [`RestRouteDto`](api-reference/rest-route-dto.md)
- [`RestResponse`](api-reference/rest-response.md)
- [`AbstractRestController`](api-reference/abstract-rest-controller.md)
- [`RestServerInterface`](api-reference/contracts.md#restserverinterface)
- [`RestRouteRegistrationException`](api-reference/exceptions.md#restrouteregistrationexception)
