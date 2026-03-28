---
title: Getting Started
description: Install Tavriia and build your first WordPress plugin module
sidebar_position: 1
---

# Getting Started

## Requirements

- PHP 8.2 or higher
- WordPress (any version compatible with your plugin)
- Composer

## Installation

Add Tavriia to your plugin via Composer:

```bash
composer require thewildfields/tavriia
```

Make sure your plugin loads Composer's autoloader:

```php
// plugin-name.php (main plugin file)
require_once __DIR__ . '/vendor/autoload.php';
```

---

## Your First Module

All plugin functionality is organized into **modules** — classes that extend `AbstractModule` and register WordPress hooks.

```php
<?php

declare(strict_types=1);

namespace MyPlugin\Modules;

use TheWildFields\Tavriia\AbstractModule;
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Post\PostRepository;
use TheWildFields\Tavriia\DTO\PostDTO;

final class EventsModule extends AbstractModule
{
    public function __construct(
        private readonly PostFactory $postFactory,
        private readonly PostRepository $postRepository,
    ) {}

    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType(): void
    {
        register_post_type('event', [
            'label'  => 'Events',
            'public' => true,
        ]);
    }
}
```

### Boot the module

In your plugin's bootstrap code, instantiate and boot each module:

```php
$eventsModule = new EventsModule(
    new PostFactory(),
    new PostRepository(),
);
$eventsModule->boot();
```

`boot()` calls `register_hooks()` internally. If you use a dependency injection container (e.g. PHP-DI, Laravel Container), wire up your modules there instead.

---

## Creating Posts

Use `PostFactory` with a `PostDTO` to create posts:

```php
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

$dto = new PostDTO(
    title: 'Summer Concert 2026',
    content: 'Annual outdoor concert in the park.',
    status: 'publish',
    postType: 'event',
    meta: [
        'venue_id'   => 42,
        'start_date' => '2026-07-15',
    ],
);

try {
    $eventId = $this->postFactory->create($dto);
} catch (PostNotFoundException $e) {
    // Creation failed — log or handle
}
```

---

## Reading Posts

Use `PostRepository` to fetch posts as typed DTOs:

```php
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

try {
    $event = $this->postRepository->findById($eventId);
    echo $event->title;
    echo $event->status;
} catch (PostNotFoundException $e) {
    // Post does not exist
}
```

---

## Querying Posts

Use `QueryBuilder` for fluent, type-safe queries:

```php
use TheWildFields\Tavriia\Query\QueryBuilder;

$results = (new QueryBuilder())
    ->postType('event')
    ->metaQuery('venue_id', 42)
    ->orderBy('date', 'DESC')
    ->limit(10)
    ->get();

echo "Found {$results->totalPosts()} events";

foreach ($results as $post) {
    echo $post->title . "\n";
}
```

---

## Next Steps

- [Core Concepts](core-concepts.md) — understand the design principles
- [Posts & Meta](posts.md) — full post and meta API
- [Taxonomy & Terms](taxonomy.md) — term management
- [Query Builder](query-builder.md) — advanced querying
- [HTTP Client](http-client.md) — making external API calls
- [API Reference](api-reference/index.md) — complete method signatures
