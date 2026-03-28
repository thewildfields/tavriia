<?php

/**
 * WordPress function stubs with correct named-parameter signatures.
 *
 * This file is required AFTER Patchwork loads so Patchwork can intercept
 * and redefine these functions in tests. The parameter names must match
 * what the source code passes as named arguments.
 */

if (!function_exists('add_menu_page')) {
    function add_menu_page(
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        ?callable $callback = null,
        string $icon_url = '',
        int|float|null $position = null,
    ): string {
        return '';
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page(
        string $parent_slug,
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        ?callable $callback = null,
        ?int $position = null,
    ): string|false {
        return false;
    }
}
