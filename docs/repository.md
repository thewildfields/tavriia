---
title: Repositories
description: Build domain repositories that compose WordPress posts and remote APIs into a single typed source
sidebar_position: 10
---

# Repositories

Tavriia ships generic building blocks for *domain* repositories — the classes your plugin writes to expose entities like `Event`, `Venue`, or `Artist`. A domain repository typically sits on top of one or more of the framework's WordPress and HTTP wrappers and returns your own DTOs.

This page shows the contract, the abstract base class, and a worked `EventsRepository` example that reads from both the WordPress database and a remote API.

---

## Classes

| Class | Role |
|-------|------|
| `RepositoryInterface<TEntity>` | Generic contract for domain repositories |
| `AbstractRepository<TEntity>` | Template-method base handling the load-or-throw contract |
| `EntityNotFoundException` | Thrown by `findById()` when no entity exists for the identifier |

---

## Contract

```php
namespace TheWildFields\Tavriia\Contracts;

/** @template TEntity of object */
interface RepositoryInterface
{
    /** @return TEntity @throws EntityNotFoundException */
    public function findById(int|string $id): object;

    /** @return TEntity|null */
    public function findByIdOrNull(int|string $id): ?object;

    /** @return list<TEntity> */
    public function all(): array;
}
```

Two rules, consistent with the rest of the framework:

1. **Repositories return DTOs, never raw WordPress objects or `WP_Error`.**
2. **`findById()` throws `EntityNotFoundException` instead of returning null.** Callers that need non-throwing behaviour use `findByIdOrNull()`.

---

## AbstractRepository

`AbstractRepository` implements the throwing contract once so consumers don't repeat it. Extend it and implement the two protected template methods:

```php
abstract protected function loadById(int|string $id): ?object; // returns TEntity|null
abstract protected function loadAll(): array;                  // returns list<TEntity>
```

The base class wires these into the public API:

- `findById()` calls `loadById()` and throws `EntityNotFoundException` when it returns null.
- `findByIdOrNull()` forwards to `loadById()` unchanged.
- `all()` forwards to `loadAll()`.
- `entityName()` supplies the human-readable name used in exception messages; it defaults to the class short-name with a trailing `Repository` trimmed (so `EventsRepository` → `Events`). Override it if you want something different.

---

## Full Example: EventsRepository

A consumer `EventsRepository` that composes:

- `PostRepository` + `MetaManager` for events already stored in WordPress
- a custom `EventsApiProvider` (extending `AbstractApiProvider`) for events fetched from an upstream API
- a plugin-local `EventDto` with a `fromApi()` named constructor

```php
namespace MyPlugin\Events;

use TheWildFields\Tavriia\Exceptions\PostNotFoundException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;
use TheWildFields\Tavriia\Post\PostRepository;
use TheWildFields\Tavriia\Repository\AbstractRepository;

/**
 * @extends AbstractRepository<EventDto>
 */
final class EventsRepository extends AbstractRepository
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly EventsApiProvider $api,
    ) {}

    protected function loadById(int|string $id): ?EventDto
    {
        if (is_int($id) || ctype_digit((string) $id)) {
            try {
                $post = $this->posts->findById((int) $id);

                return EventDto::fromPost($post, $this->posts->metaFor($post->id));
            } catch (PostNotFoundException) {
                // fall through to the API — the ID might be a remote event
            }
        }

        try {
            $payload = $this->api->getEvent((string) $id);

            return EventDto::fromApi($payload);
        } catch (ApiResponseException $e) {
            if ($e->getResponse()->statusCode === 404) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @return list<EventDto>
     */
    protected function loadAll(): array
    {
        $local  = $this->fromWordPress();
        $remote = $this->fromApi();

        return [...$local, ...$remote];
    }

    /**
     * @return list<EventDto>
     */
    private function fromWordPress(): array
    {
        $args = (new \TheWildFields\Tavriia\Query\QueryBuilder())
            ->postType('event')
            ->postStatus('publish')
            ->limit(100)
            ->get()
            ->toArgs();

        return array_map(
            fn ($post) => EventDto::fromPost($post, $this->posts->metaFor($post->id)),
            $this->posts->findMany($args),
        );
    }

    /**
     * @return list<EventDto>
     */
    private function fromApi(): array
    {
        return array_map(
            static fn (array $payload): EventDto => EventDto::fromApi($payload),
            $this->api->listEvents(),
        );
    }
}
```

### Using it

```php
use MyPlugin\Events\EventsRepository;
use TheWildFields\Tavriia\Exceptions\EntityNotFoundException;

try {
    $event = $eventsRepository->findById($id);
} catch (EntityNotFoundException $e) {
    // Not in WordPress, not in the upstream API — 404 your REST endpoint.
}

$all = $eventsRepository->all();
```

---

## Notes on source composition

`AbstractRepository` does not prescribe *how* sources are combined. The decision — cache-first, DB-first, remote-first, merge strategies, pagination — belongs in the concrete repository because it is domain logic. The framework's job is to enforce the throwing contract and give you a typed generic so static analysers can verify the DTO types flowing through your code.

Common patterns you might implement inside `loadById()` / `loadAll()`:

- **DB-first with API fallback** — try `PostRepository`, fall back to the API provider on `PostNotFoundException`.
- **Read-through cache** — check a transient, call the API on miss, persist the result, return.
- **Merge + dedupe** — fetch from both sources, key by a stable external ID, prefer one side on conflict.

Each of these is a few lines of explicit code in your repository. Don't reach for a generic "hybrid" abstraction when a concrete method will do.

---

## API Reference

- [`RepositoryInterface`](api-reference/contracts.md#repositoryinterface)
- [`AbstractRepository`](api-reference/abstract-repository.md)
- [`EntityNotFoundException`](api-reference/exceptions.md#entitynotfoundexception)
