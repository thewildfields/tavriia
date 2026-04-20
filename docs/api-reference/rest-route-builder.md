---
title: RestRouteBuilder
description: API reference for TheWildFields\Tavriia\Rest\RestRouteBuilder
class: RestRouteBuilder
namespace: TheWildFields\Tavriia\Rest
type: final-class
sidebar_position: 17
---

# RestRouteBuilder

```
TheWildFields\Tavriia\Rest\RestRouteBuilder
```

Fluent, immutable builder for `RestRouteDto` instances. Each configuration method returns a new instance, so partial builders can be reused without shared state.

---

## Class Signature

```php
final class RestRouteBuilder
```

---

## Constructor

```php
public function __construct(
    string $namespace,
    string $route,
)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$namespace` | `string` | REST namespace, e.g. `'my-plugin/v1'` |
| `$route` | `string` | Route pattern, e.g. `'/events/(?P<id>\d+)'` |

---

## Methods

### `methods(string $methods): self`

Sets the HTTP methods this route responds to. Accepts a single method or a comma-separated list.

```php
$builder->methods('GET');
$builder->methods('GET, POST');
```

---

### HTTP verb shortcuts

Each shortcut sets the method and callback in one call.

```php
public function get(callable $callback): self
public function post(callable $callback): self
public function put(callable $callback): self
public function patch(callable $callback): self
public function delete(callable $callback): self
```

**Example:**
```php
(new RestRouteBuilder('my-plugin/v1', '/events'))
    ->get([$controller, 'list']);
```

---

### `callback(callable $callback): self`

Sets the request-handling callback directly. Prefer the verb shortcuts unless you're setting `methods()` separately.

```php
$builder->callback([$controller, 'handle']);
```

---

### `permissionCallback(callable $callback): self`

Sets the permission callback used to authorize the request. WordPress emits a `_doing_it_wrong()` notice for routes without an explicit permission callback, so this is effectively required.

```php
$builder->permissionCallback(fn() => current_user_can('manage_options'));
```

---

### `public(): self`

Shortcut for `permissionCallback('__return_true')`. Marks the route as publicly accessible.

```php
$builder->public();
```

---

### `arg(string $name, array $definition): self`

Declares a single argument in the route's argument schema.

```php
$builder->arg('id', [
    'type'              => 'integer',
    'required'          => true,
    'sanitize_callback' => 'absint',
]);
```

Can be called multiple times; each call appends to the schema.

---

### `args(array $args): self`

Replaces the entire argument schema map.

```php
$builder->args([
    'id'     => ['type' => 'integer', 'required' => true],
    'expand' => ['type' => 'string',  'default'  => 'none'],
]);
```

---

### `schema(callable $schema): self`

Attaches a schema callback to the route.

```php
$builder->schema(fn() => [
    'type'       => 'object',
    'properties' => [...],
]);
```

---

### `override(bool $override = true): self`

Toggles the "override existing registration" flag passed to `register_rest_route()`.

```php
$builder->override();        // override = true
$builder->override(false);   // override = false
```

---

### `build(): RestRouteDto`

Builds and returns the immutable `RestRouteDto`.

```php
public function build(): RestRouteDto
```

**Throws:**

- `RestRouteRegistrationException` — if the namespace, route, callback, or permission callback is missing.

---

## Complete Example

```php
use TheWildFields\Tavriia\Rest\RestRouteBuilder;

$route = (new RestRouteBuilder('my-plugin/v1', '/events/(?P<id>\d+)'))
    ->get([$controller, 'show'])
    ->public()
    ->arg('id', [
        'type'     => 'integer',
        'required' => true,
    ])
    ->arg('expand', [
        'type'    => 'string',
        'default' => 'none',
    ])
    ->build();

$server->register($route);
```

---

## See Also

- [REST API guide](../rest.md)
- [`RestServer`](rest-server.md)
- [`RestRouteDto`](rest-route-dto.md)
- [`RestResponse`](rest-response.md)
