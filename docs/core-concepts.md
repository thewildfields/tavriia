---
title: Core Concepts
description: The design principles and patterns that Tavriia is built on
sidebar_position: 2
---

# Core Concepts

Tavriia is built on a small set of firm principles. Understanding them makes everything else predictable.

---

## 1. WP_Error Never Escapes the Framework

Every WordPress function that can return `WP_Error` is wrapped at the framework boundary. WordPress errors become typed PHP exceptions before they reach your code.

**Plugin code never calls `is_wp_error()`.**

```php
// Wrong — leaking WP internals into plugin code
$result = wp_insert_post($args);
if (is_wp_error($result)) {
    // ...
}

// Right — framework converts WP_Error to a typed exception
try {
    $id = $this->postFactory->create($dto);
} catch (PostNotFoundException $e) {
    // handle failure
}
```

This boundary means your plugin code is completely isolated from WordPress error semantics. You write PHP, not WordPress-flavored PHP.

---

## 2. Factories Return IDs, Repositories Return DTOs

There is a deliberate separation between write and read operations:

| Class | Method | Returns | Throws |
|-------|--------|---------|--------|
| `PostFactory` | `create(PostDTO)` | `int` (post ID) | `PostNotFoundException` |
| `PostFactory` | `update(int, PostDTO)` | `int` (post ID) | `PostNotFoundException` |
| `PostRepository` | `findById(int)` | `PostDTO` | `PostNotFoundException` |
| `PostRepository` | `findMany(QueryArgsDTO)` | `PostDTO[]` | — |
| `TermFactory` | `create(TaxonomyDTO)` | `int` (term ID) | `TermNotFoundException` |
| `TermRepository` | `findById(int, string)` | `TaxonomyDTO` | `TermNotFoundException` |

**Factories** own creation and mutation. They accept DTOs as input and return primitive IDs.

**Repositories** own retrieval. They return typed DTOs or throw if the record does not exist.

---

## 3. All Concrete Classes Are `final`

Every concrete wrapper class is declared `final`. This forces composition over inheritance and makes the framework's surface area explicit.

The only non-final classes are:
- `AbstractModule` — designed for extension by plugin modules
- `AbstractMetaManager` — base class for `MetaManager` and `TermMetaManager`

If you need to extend framework behavior, use contracts (interfaces) and dependency injection rather than subclassing.

---

## 4. Readonly DTOs

All Data Transfer Objects (DTOs) use PHP 8.2 `readonly` properties. They are immutable value objects.

```php
final readonly class PostDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $status,
        public string $postType,
        public array  $meta = [],
        // ...
    ) {}

    public static function fromWpPost(\WP_Post $post): self { ... }
}
```

Key characteristics:
- **Constructed once, never mutated.** To "update" a DTO, create a new one.
- **Named constructors** (`fromWpPost`, `fromWpTerm`) wrap WordPress objects into typed DTOs.
- **`withId(int $id)`** returns a new instance with only the ID field changed, used after creation.

---

## 5. Typed Meta Accessors

`MetaManager` and `TermMetaManager` expose typed getters instead of raw `get_post_meta()` values:

```php
$meta = $this->postRepository->metaFor($postId);

$name    = $meta->getString('venue_name');        // string
$capacity = $meta->getInt('capacity');            // int
$isOpen  = $meta->getBool('is_open');             // bool
$tags    = $meta->getArray('tags');               // array
$price   = $meta->getFloat('ticket_price');       // float
```

All accessors accept a default value and return it when the meta key is absent or empty. You never receive `false`, `''`, or `null` unexpectedly from a meta read.

---

## 6. Fluent, Immutable QueryBuilder

`QueryBuilder` is a fluent, immutable builder. Every method returns a clone with the applied change — the original is untouched.

```php
$base = (new QueryBuilder())
    ->postType('event')
    ->status('publish');

// Two different queries, $base unchanged
$upcoming = $base->metaQuery('start_date', date('Y-m-d'), '>=')->get();
$past     = $base->metaQuery('start_date', date('Y-m-d'), '<')->get();
```

The builder never executes until you call `->get()`, which returns a `QueryResult`.

---

## 7. Module Boot Contract

Plugin modules extend `AbstractModule` and must implement `register_hooks()`. The framework calls `boot()` to initialize the module, which in turn calls `register_hooks()`.

```php
final class VenuesModule extends AbstractModule
{
    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_filter('the_title', [$this, 'maybeAppendCity'], 10, 2);
    }
}
```

Modules receive all dependencies through the constructor. They do not instantiate services internally. This keeps modules testable in isolation.

---

## Contracts and Interfaces

Every major wrapper class has a corresponding interface in `TheWildFields\Tavriia\Contracts\`:

| Interface | Implemented by |
|-----------|---------------|
| `HasHooksInterface` | `AbstractModule` |
| `ApiClientInterface` | `HttpClient` |
| `PostRepositoryInterface` | `PostRepository` |
| `TaxonomyRepositoryInterface` | `TermRepository` |
| `QueryBuilderInterface` | `QueryBuilder` |

Type-hint against interfaces in your module constructors to keep your plugin decoupled from concrete implementations.

---

## No External Dependencies

Tavriia has zero production dependencies beyond WordPress core. Dev dependencies (PHPUnit, Mockery, Brain Monkey) are used for the test suite only.
