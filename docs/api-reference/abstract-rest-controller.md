---
title: AbstractRestController
description: API reference for TheWildFields\Tavriia\Rest\AbstractRestController
class: AbstractRestController
namespace: TheWildFields\Tavriia\Rest
type: abstract-class
sidebar_position: 19
---

# AbstractRestController

```
TheWildFields\Tavriia\Rest\AbstractRestController
```

Base class for grouping related REST endpoints. Implementations declare their routes via the `routes()` method; the framework handles hooking into `rest_api_init` and registering them through the injected `RestServer`.

Implements [`HasHooksInterface`](contracts.md#hashooksinterface).

---

## Class Signature

```php
abstract class AbstractRestController implements HasHooksInterface
```

---

## Constructor

```php
public function __construct(RestServerInterface $server)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$server` | `RestServerInterface` | The server used to register routes. Usually a `RestServer` instance |

Subclasses override the constructor to accept additional dependencies (repositories, factories, etc.) and must call `parent::__construct($server)`.

---

## Methods

### `register_hooks(): void` — `final`

Registers the `rest_api_init` action callback. Cannot be overridden.

Part of the `HasHooksInterface` contract — call this from your plugin bootstrap, typically via the module system.

```php
final public function register_hooks(): void
```

---

### `registerRoutes(): void` — `final`

Invoked by WordPress during `rest_api_init`. Iterates over `routes()` and passes the result to `RestServer::registerMany()`.

```php
final public function registerRoutes(): void
```

Exposed publicly so WordPress can call it as a hook callback; plugin code should not call it directly.

---

### `routes(): iterable` — `abstract protected`

Return the route definitions this controller exposes. Implementations can return an array or use a generator with `yield`.

```php
abstract protected function routes(): iterable;
```

**Returns:** `iterable<RestRouteDto>`

---

## Complete Example

```php
use TheWildFields\Tavriia\Dto\RestRouteDto;
use TheWildFields\Tavriia\Rest\AbstractRestController;
use TheWildFields\Tavriia\Rest\RestRouteBuilder;
use TheWildFields\Tavriia\Rest\RestResponse;
use TheWildFields\Tavriia\Rest\RestServer;
use TheWildFields\Tavriia\Post\PostRepository;

final class EventsController extends AbstractRestController
{
    public function __construct(
        RestServer $server,
        private readonly PostRepository $repository,
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
    }

    public function list(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        return RestResponse::ok($this->repository->findMany($args))->toWp();
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
}
```

And wire it up during plugin bootstrap:

```php
$controller = new EventsController($restServer, $postRepository);
$controller->register_hooks(); // rest_api_init action is now wired
```

---

## See Also

- [REST API guide](../rest.md)
- [`RestServer`](rest-server.md)
- [`RestRouteBuilder`](rest-route-builder.md)
- [`RestResponse`](rest-response.md)
- [`HasHooksInterface`](contracts.md#hashooksinterface)
