<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Contracts;

/**
 * Contract for any class that registers WordPress hooks.
 */
interface HasHooksInterface
{
    /**
     * Register all WordPress actions and filters.
     */
    public function register_hooks(): void;
}
