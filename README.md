# Tavriia

A clean, typed, PSR-compliant OOP abstraction layer over WordPress core functions. Ships as a Composer package consumed by WordPress plugins.

[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![PSR-12](https://img.shields.io/badge/PSR-12-brightgreen)](https://www.php-fig.org/psr/psr-12/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## What is Tavriia?

Tavriia is a framework that provides typed wrappers, contracts, and DTOs over raw WordPress APIs. It is **not a plugin**. It is a Composer library you pull into your plugin projects.

**Without Tavriia:**
```php
$post = get_post($id);
if (!$post instanceof \WP_Post) {
    // handle missing
}
$meta = get_post_meta($id, 'some_field', true);
$result = wp_insert_post($args);
if (is_wp_error($result)) {
    // handle error
}
```

**With Tavriia:**
```php
$post = $this->postRepository->findById($id); // throws PostNotFoundException or returns PostDTO
$meta = $this->postRepository->metaFor($id)->getString('some_field');
$newId = $this->postFactory->create($dto); // throws or returns int
```

---

## Installation

```bash
composer require thewildfields/tavriia
```

Requires PHP 8.2+ and WordPress (no minimum version enforced).

---

## Quick Start

### 1. Create a module

```php
use TheWildFields\Tavriia\AbstractModule;
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Post\PostRepository;

final class EventsModule extends AbstractModule
{
    public function __construct(
        private readonly PostFactory $postFactory,
        private readonly PostRepository $postRepository,
    ) {}

    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('save_post_event', [$this, 'onSaveEvent'], 10, 2);
    }
}
```

### 2. Create posts with typed DTOs

```php
use TheWildFields\Tavriia\DTO\PostDTO;

$dto = new PostDTO(
    title: 'Summer Concert',
    content: 'Annual summer concert in the park.',
    status: 'publish',
    postType: 'event',
    meta: ['venue_id' => 42],
);

$eventId = $this->postFactory->create($dto);
```

### 3. Query posts fluently

```php
use TheWildFields\Tavriia\Query\QueryBuilder;

$results = (new QueryBuilder())
    ->postType('event')
    ->metaQuery('venue_id', 42)
    ->orderBy('date', 'DESC')
    ->limit(10)
    ->get();

foreach ($results as $post) {
    echo $post->title;
}
```

### 4. Make HTTP requests

```php
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;

$client = new HttpClient(new ResponseProcessor());

$response = $client->get('https://api.example.com/events');
$data = $response->json();
```

---

## Features

| Area | Classes | Wraps |
|------|---------|-------|
| **Posts** | `PostFactory`, `PostRepository`, `MetaManager` | `wp_insert_post`, `wp_update_post`, `get_post`, `get_post_meta` |
| **Taxonomy** | `TermFactory`, `TermRepository`, `TermMetaManager` | `wp_insert_term`, `get_terms`, `wp_get_object_terms` |
| **Queries** | `QueryBuilder`, `QueryResult` | `WP_Query`, `get_posts` |
| **HTTP** | `HttpClient`, `RequestBuilder`, `ResponseProcessor` | `wp_remote_get`, `wp_remote_post`, `wp_remote_request` |
| **Admin** | `AdminMenuPage`, `AdminNotice` | `add_menu_page`, `admin_notices` |
| **Modules** | `AbstractModule` | WordPress hooks lifecycle |

---

## Design Principles

**WP_Error never escapes the framework.** Every wrapper converts `WP_Error` into a typed exception at the WordPress boundary. Plugin code never calls `is_wp_error()`.

**Factories return IDs, repositories return DTOs.** `PostFactory::create()` returns `int`. `PostRepository::findById()` returns `PostDTO` or throws `PostNotFoundException`.

**All concrete classes are `final`.** Only `AbstractModule` and abstract base classes are non-final.

**All DTOs are `readonly`.** Immutable value objects with named constructors where appropriate.

**Typed meta accessors.** `MetaManager::getString()`, `::getInt()`, `::getBool()`, `::getArray()` — never raw meta values.

---

## Documentation

- [Getting Started](docs/getting-started.md)
- [Core Concepts](docs/core-concepts.md)
- [Modules](docs/modules.md)
- [Posts & Meta](docs/posts.md)
- [Taxonomy & Terms](docs/taxonomy.md)
- [Query Builder](docs/query-builder.md)
- [HTTP Client](docs/http-client.md)
- [Admin Helpers](docs/admin.md)
- [Exception Handling](docs/exceptions.md)
- [API Reference](docs/api-reference/index.md)

---

## Contributing

This is a private framework package for The Wild Fields. See [CLAUDE.md](CLAUDE.md) for architectural guidelines.

---

## License

MIT
