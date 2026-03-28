<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia;

use TheWildFields\Tavriia\Contracts\HasHooksInterface;

/**
 * Abstract base class for all plugin modules.
 *
 * Consuming plugins extend this class and implement register_hooks()
 * to wire up WordPress actions and filters. The framework handles
 * the boot lifecycle — dependency resolution and initialization order.
 */
abstract class AbstractModule implements HasHooksInterface
{
    /**
     * Boot the module by registering its hooks.
     *
     * Called by the framework during the plugin bootstrap phase.
     * Subclasses must not override this method; override register_hooks() instead.
     */
    final public function boot(): void
    {
        $this->register_hooks();
    }

    /**
     * Register all WordPress actions and filters for this module.
     *
     * Implementing classes must use add_action() / add_filter() here.
     * Do NOT call register_post_type() or register_taxonomy() directly
     * from this method — hook them via add_action('init', ...) instead.
     */
    abstract public function register_hooks(): void;
}
