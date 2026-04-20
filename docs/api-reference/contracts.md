---
title: Contracts (Interfaces)
description: API reference for all Tavriia contract interfaces
namespace: TheWildFields\Tavriia\Contracts
sidebar_position: 31
---

# Contracts

All framework interfaces live in `TheWildFields\Tavriia\Contracts\`. Type-hint against these interfaces in your module constructors to keep plugin code decoupled from concrete implementations.

---

## HasHooksInterface

```
TheWildFields\Tavriia\Contracts\HasHooksInterface
```

Contract for classes that register WordPress hooks.

```php
interface HasHooksInterface
{
    public function register_hooks(): void;
}
```

Implemented by: `AbstractModule`

---

## ApiClientInterface

```
TheWildFields\Tavriia\Contracts\ApiClientInterface
```

Contract for HTTP API clients.

```php
interface ApiClientInterface
{
    public function get(string $url, array $args = []): ApiResponseDTO;
    public function post(string $url, array $args = []): ApiResponseDTO;
    public function request(ApiRequestDTO $request): ApiResponseDTO;
}
```

Implemented by: `HttpClient`

**All methods throw:**
- `ApiRequestException` on transport-level failure
- `ApiResponseException` on non-2xx response

**Usage:**
```php
final class SyncModule extends AbstractModule
{
    public function __construct(
        private readonly ApiClientInterface $apiClient,
    ) {}
}
```

Inject `new HttpClient(new ResponseProcessor())` in production. In tests, mock `ApiClientInterface`.

---

## PostRepositoryInterface

```
TheWildFields\Tavriia\Contracts\PostRepositoryInterface
```

Contract for post retrieval.

```php
interface PostRepositoryInterface
{
    public function findById(int $id): PostDTO;
    public function findMany(QueryArgsDTO $args): array; // PostDTO[]
}
```

Implemented by: `PostRepository`

**Throws:** `PostNotFoundException` from `findById()`.

`findMany()` never throws — returns `[]` when no posts match.

---

## TaxonomyRepositoryInterface

```
TheWildFields\Tavriia\Contracts\TaxonomyRepositoryInterface
```

Contract for taxonomy term retrieval.

```php
interface TaxonomyRepositoryInterface
{
    public function findById(int $id, string $taxonomy): TaxonomyDTO;
    public function findBy(string $field, string|int $value, string $taxonomy): TaxonomyDTO;
    public function findAll(string $taxonomy, array $args = []): array; // TaxonomyDTO[]
    public function findByObject(int $objectId, string $taxonomy): array; // TaxonomyDTO[]
}
```

Implemented by: `TermRepository`

**Throws:** `TermNotFoundException` from `findById()`, `findBy()`, and `findByObject()` (on WP_Error).

`findAll()` never throws — returns `[]` on error.

---

## QueryBuilderInterface

```
TheWildFields\Tavriia\Contracts\QueryBuilderInterface
```

Contract for fluent query builders.

```php
interface QueryBuilderInterface
{
    public function postType(string $postType): static;
    public function metaQuery(string $key, mixed $value, string $compare = '='): static;
    public function orderBy(string $orderBy, string $order = 'DESC'): static;
    public function limit(int $limit): static;
    public function get(): QueryResult;
}
```

Implemented by: `QueryBuilder`

All builder methods return `static` (the implementing class), enabling fluent chains. `get()` executes the query and returns a `QueryResult`.

---

## RestServerInterface

```
TheWildFields\Tavriia\Contracts\RestServerInterface
```

Contract for classes that register REST API routes with WordPress.

```php
interface RestServerInterface
{
    public function register(RestRouteDto $route): void;
    public function registerMany(iterable $routes): void;
}
```

Implemented by: `RestServer`

**Throws:** `RestRouteRegistrationException` when a route cannot be registered (empty namespace/route, or WordPress reports failure).

Type-hint against this interface in `AbstractRestController` subclasses to keep controllers decoupled from the concrete implementation.

---

## See Also

- [`AbstractModule`](abstract-module.md)
- [`HttpClient`](http-client.md)
- [`PostRepository`](post-repository.md)
- [`TermRepository`](term-repository.md)
- [`QueryBuilder`](query-builder.md)
- [`RestServer`](rest-server.md)
