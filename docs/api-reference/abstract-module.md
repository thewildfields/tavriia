---
title: AbstractModule
description: API reference for TheWildFields\Tavriia\AbstractModule
class: AbstractModule
namespace: TheWildFields\Tavriia
type: abstract-class
implements: HasHooksInterface
sidebar_position: 1
---

# AbstractModule

```
TheWildFields\Tavriia\AbstractModule
```

Abstract base class for all plugin modules. Implements `HasHooksInterface`. Extend this class in consuming plugins to define WordPress hook registrations.

---

## Class Signature

```php
abstract class AbstractModule implements HasHooksInterface
```

---

## Methods

### `boot(): void`

Initializes the module by calling `register_hooks()`.

```php
public function boot(): void
```

Call `boot()` once during your plugin's initialization phase. This is the entry point for the module lifecycle.

**Example:**
```php
$module = new EventsModule($postFactory, $postRepository);
$module->boot();
```

---

### `register_hooks(): void`

Abstract. Must be implemented by subclasses. Register all WordPress hooks here using `add_action()` and `add_filter()`.

```php
abstract public function register_hooks(): void;
```

**Example implementation:**
```php
public function register_hooks(): void
{
    add_action('init', [$this, 'registerPostType']);
    add_action('save_post_event', [$this, 'onSave'], 10, 2);
    add_filter('the_content', [$this, 'appendMeta']);
}
```

---

## Usage

### Basic Module

```php
use TheWildFields\Tavriia\AbstractModule;

final class EventsModule extends AbstractModule
{
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

### Module with Dependencies

```php
use TheWildFields\Tavriia\AbstractModule;
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Post\PostRepository;

final class EventsModule extends AbstractModule
{
    public function __construct(
        private readonly PostFactory    $postFactory,
        private readonly PostRepository $postRepository,
    ) {}

    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('save_post_event', [$this, 'onSave'], 10, 2);
    }
}
```

---

## Notes

- Only `AbstractModule` (and `AbstractMetaManager`) are non-final in the framework. All other classes are `final`.
- The constructor is not defined in `AbstractModule`. Subclasses may define any constructor signature.
- Do not call WordPress functions in the constructor. Only use `add_action()` / `add_filter()` inside `register_hooks()`.

---

## See Also

- [Modules guide](../modules.md)
- [`HasHooksInterface`](contracts.md#hashooksinterface)
