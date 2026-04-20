---
title: RestRouteDto
description: API reference for TheWildFields\Tavriia\Dto\RestRouteDto
class: RestRouteDto
namespace: TheWildFields\Tavriia\Dto
type: final-readonly-class
sidebar_position: 25
---

# RestRouteDto

```
TheWildFields\Tavriia\Dto\RestRouteDto
```

Immutable data transfer object describing a single WordPress REST API route. This is the typed equivalent of the array passed to `register_rest_route()`.

Plugin code rarely constructs a `RestRouteDto` directly — use [`RestRouteBuilder`](rest-route-builder.md) instead.

---

## Class Signature

```php
final readonly class RestRouteDto
```

---

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `RestRouteDto::METHOD_GET` | `'GET'` | GET method |
| `RestRouteDto::METHOD_POST` | `'POST'` | POST method |
| `RestRouteDto::METHOD_PUT` | `'PUT'` | PUT method |
| `RestRouteDto::METHOD_PATCH` | `'PATCH'` | PATCH method |
| `RestRouteDto::METHOD_DELETE` | `'DELETE'` | DELETE method |
| `RestRouteDto::METHODS_ALL` | `'GET, POST, PUT, PATCH, DELETE'` | All standard methods |

---

## Constructor

```php
public function __construct(
    public string $namespace,
    public string $route,
    public string $methods,
    public mixed $callback,
    public mixed $permissionCallback,
    public array $args = [],
    public mixed $schema = null,
    public bool $override = false,
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$namespace` | `string` | — | REST namespace (e.g. `'my-plugin/v1'`) |
| `$route` | `string` | — | Route pattern (e.g. `'/events/(?P<id>\d+)'`) |
| `$methods` | `string` | — | HTTP methods, comma-separated (e.g. `'GET, POST'`) |
| `$callback` | `callable` | — | Callable handling matching requests |
| `$permissionCallback` | `callable` | — | Callable authorizing the request |
| `$args` | `array<string, array>` | `[]` | Argument schema for parameter validation |
| `$schema` | `callable\|null` | `null` | Optional schema callback |
| `$override` | `bool` | `false` | Whether to replace an existing registration |

---

## Methods

### `toWpArgs(): array`

Builds the endpoint definition array passed as the third argument to `register_rest_route()`.

```php
public function toWpArgs(): array
```

**Returns:** `array<string, mixed>` containing:

- `methods` — the HTTP methods string
- `callback` — the request callback
- `permission_callback` — the permission callback (note snake_case key)
- `args` — the argument schema
- `schema` — only present when a schema callback was provided

The `$namespace`, `$route`, and `$override` fields are passed separately by `RestServer::register()` and do **not** appear in the returned array.

**Example:**
```php
$dto = new RestRouteDto(
    namespace: 'my-plugin/v1',
    route: '/events',
    methods: 'GET',
    callback: [$controller, 'list'],
    permissionCallback: '__return_true',
);

register_rest_route(
    $dto->namespace,
    $dto->route,
    $dto->toWpArgs(),
    $dto->override,
);
```

---

## See Also

- [REST API guide](../rest.md)
- [`RestRouteBuilder`](rest-route-builder.md)
- [`RestServer`](rest-server.md)
