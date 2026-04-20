---
title: RestServer
description: API reference for TheWildFields\Tavriia\Rest\RestServer
class: RestServer
namespace: TheWildFields\Tavriia\Rest
type: final-class
sidebar_position: 16
---

# RestServer

```
TheWildFields\Tavriia\Rest\RestServer
```

Registers REST API routes with WordPress. Wraps `register_rest_route()` and converts its boolean return value into exceptions.

Implements [`RestServerInterface`](contracts.md#restserverinterface).

---

## Class Signature

```php
final class RestServer implements RestServerInterface
```

---

## Constructor

```php
public function __construct()
```

Takes no arguments.

---

## Methods

### `register(RestRouteDto $route): void`

Registers a single route with WordPress.

```php
public function register(RestRouteDto $route): void
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$route` | `RestRouteDto` | Immutable route definition, usually produced by `RestRouteBuilder` |

**Throws:**

- `RestRouteRegistrationException` — if the namespace or route pattern is empty, or if WordPress reports failure.

**Must be called inside a `rest_api_init` callback.** Calling it earlier silently no-ops because WordPress has not yet initialized its REST infrastructure.

**Example:**
```php
use TheWildFields\Tavriia\Rest\RestRouteBuilder;
use TheWildFields\Tavriia\Rest\RestServer;

$server = new RestServer();

add_action('rest_api_init', function () use ($server) {
    $route = (new RestRouteBuilder('my-plugin/v1', '/events'))
        ->get([$controller, 'list'])
        ->public()
        ->build();

    $server->register($route);
});
```

---

### `registerMany(iterable $routes): void`

Registers multiple routes in sequence.

```php
public function registerMany(iterable $routes): void
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$routes` | `iterable<RestRouteDto>` | Array, generator, or any other iterable of route definitions |

**Throws:**

- `RestRouteRegistrationException` — on the first failing registration. Previously-registered routes remain active.

**Example:**
```php
$server->registerMany([
    $listRoute,
    $showRoute,
    $createRoute,
]);

// Or with a generator:
$server->registerMany((function () use ($routes) {
    foreach ($routes as $route) {
        yield $route;
    }
})());
```

---

## See Also

- [REST API guide](../rest.md)
- [`RestRouteBuilder`](rest-route-builder.md)
- [`RestRouteDto`](rest-route-dto.md)
- [`AbstractRestController`](abstract-rest-controller.md)
- [`RestRouteRegistrationException`](exceptions.md#restrouteregistrationexception)
