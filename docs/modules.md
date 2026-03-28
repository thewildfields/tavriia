---
title: Modules
description: Building plugin modules with AbstractModule
sidebar_position: 3
---

# Modules

All plugin functionality is organized into **modules** — cohesive units of behavior that group related hooks, actions, and filters together. Tavriia provides `AbstractModule` as the base class for all modules.

---

## AbstractModule

```
TheWildFields\Tavriia\AbstractModule
```

`AbstractModule` implements `HasHooksInterface` and defines the module lifecycle. Subclasses must implement one method: `register_hooks()`.

```php
use TheWildFields\Tavriia\AbstractModule;

final class EventsModule extends AbstractModule
{
    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('save_post_event', [$this, 'syncToCalendar'], 10, 2);
        add_filter('the_content', [$this, 'appendEventMeta']);
    }
}
```

### Booting a Module

Call `boot()` to initialize the module. This triggers `register_hooks()` internally.

```php
$module = new EventsModule(/* dependencies */);
$module->boot();
```

Boot each module once, typically during the plugin's main initialization phase.

---

## Dependency Injection

Modules receive all dependencies through the constructor. Never instantiate framework services inside a module method — always inject them.

```php
use TheWildFields\Tavriia\AbstractModule;
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Post\PostRepository;
use TheWildFields\Tavriia\Taxonomy\TermRepository;
use TheWildFields\Tavriia\Query\QueryBuilder;

final class EventsModule extends AbstractModule
{
    public function __construct(
        private readonly PostFactory $postFactory,
        private readonly PostRepository $postRepository,
        private readonly TermRepository $termRepository,
    ) {}

    public function register_hooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
    }
}
```

### Wiring with a DI Container

For real plugin projects, use a DI container to manage module instantiation:

```php
// Using PHP-DI or any PSR-11 compatible container
$container->set(EventsModule::class, function ($c) {
    return new EventsModule(
        $c->get(PostFactory::class),
        $c->get(PostRepository::class),
        $c->get(TermRepository::class),
    );
});

// Boot all modules
$container->get(EventsModule::class)->boot();
$container->get(VenuesModule::class)->boot();
```

### Simple Bootstrap (no container)

For smaller plugins, manual instantiation is fine:

```php
// plugin.php
add_action('plugins_loaded', function () {
    $postFactory    = new PostFactory();
    $postRepository = new PostRepository();

    (new EventsModule($postFactory, $postRepository))->boot();
    (new VenuesModule($postRepository))->boot();
});
```

---

## Multiple Modules

Organize your plugin by domain area. Each module is independently testable and has a clear responsibility boundary.

```
src/
  Modules/
    EventsModule.php     — register_post_type, save hooks
    VenuesModule.php     — venue post type hooks
    CalendarModule.php   — admin calendar page
    SyncModule.php       — external API sync logic
```

Modules should not depend on each other directly. If one module needs data that another manages, inject the relevant repository or factory.

---

## What Goes in a Module

| Belongs in a module | Does NOT belong in a module |
|---------------------|----------------------------|
| `add_action()` / `add_filter()` calls | Business logic that isn't hook-triggered |
| `register_post_type()` / `register_taxonomy()` | Framework service instantiation |
| Hook callback methods | Direct WordPress function calls (use framework wrappers) |
| Module-level configuration (passed via constructor) | Global state or singletons |

---

## Testing Modules

Because modules use constructor injection, they are straightforward to test:

```php
use PHPUnit\Framework\TestCase;
use Mockery;

class EventsModuleTest extends TestCase
{
    public function test_registers_post_type_on_init(): void
    {
        $factory    = Mockery::mock(PostFactory::class);
        $repository = Mockery::mock(PostRepository::class);

        $module = new EventsModule($factory, $repository);
        $module->boot();

        $this->assertTrue(has_action('init'));
    }
}
```

---

## API Reference

- [`AbstractModule`](api-reference/abstract-module.md)
- [`HasHooksInterface`](api-reference/contracts.md#hashooksinterface)
