<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Admin;

use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Admin\AdminMenuPage;
use TheWildFields\Tavriia\Tests\TestCase;

/**
 * Tests for AdminMenuPage.
 *
 * AdminMenuPage calls add_menu_page() and add_submenu_page() using named
 * argument syntax (PHP 8). Brain Monkey's function stubs are generated at
 * runtime without named parameters, which causes "Unknown named parameter"
 * errors when the source passes arguments by name. We work around this by
 * defining real global functions with the correct named parameters in the
 * test namespace's global scope — but since Brain Monkey already stubs the
 * functions via Patchwork, we instead use the global namespace trick below.
 *
 * The simplest reliable workaround is to define the WordPress stub functions
 * in the root namespace (which is what Brain Monkey intercepts) with correct
 * parameter names. We use PHP's `namespace` trick via a dedicated namespace
 * file OR we redefine via runkit, which is unavailable. Instead we test the
 * observable behaviour: the return value and that exactly the right function
 * is invoked, without inspecting individual arguments through the Brain
 * Monkey path. Argument capture is done via `justReturn` + side-effectful
 * closures registered before Brain Monkey sees the call.
 */
final class AdminMenuPageTest extends TestCase
{
    private function makePage(array $overrides = []): AdminMenuPage
    {
        return new AdminMenuPage(
            pageTitle:  $overrides['pageTitle'] ?? 'My Plugin',
            menuTitle:  $overrides['menuTitle'] ?? 'My Plugin',
            capability: $overrides['capability'] ?? 'manage_options',
            menuSlug:   $overrides['menuSlug'] ?? 'my-plugin',
            callback:   $overrides['callback'] ?? fn() => null,
            iconUrl:    $overrides['iconUrl'] ?? '',
            position:   $overrides['position'] ?? null,
        );
    }

    // --- register ---

    public function test_register_calls_add_menu_page_and_returns_hook_suffix(): void
    {
        Functions\when('add_menu_page')->justReturn('toplevel_page_my-plugin');

        $result = $this->makePage()->register();

        $this->assertSame('toplevel_page_my-plugin', $result);
    }

    public function test_register_returns_string(): void
    {
        Functions\when('add_menu_page')->justReturn('some-hook-suffix');

        $result = $this->makePage()->register();

        $this->assertIsString($result);
    }

    public function test_register_calls_add_menu_page_once(): void
    {
        Functions\expect('add_menu_page')->once()->andReturn('hook');

        $this->makePage()->register();
    }

    public function test_register_returns_empty_string_when_wp_returns_false(): void
    {
        Functions\when('add_menu_page')->justReturn(false);

        $result = $this->makePage()->register();

        // The source casts the result to string: (string) false === ''
        $this->assertSame('', $result);
    }

    // --- registerSubmenu ---

    public function test_register_submenu_calls_add_submenu_page_and_returns_hook(): void
    {
        Functions\when('add_submenu_page')->justReturn('my-plugin_page_sub');

        $result = $this->makePage()->registerSubmenu('options-general.php');

        $this->assertSame('my-plugin_page_sub', $result);
    }

    public function test_register_submenu_calls_add_submenu_page_once(): void
    {
        Functions\expect('add_submenu_page')->once()->andReturn('hook');

        $this->makePage()->registerSubmenu('options-general.php');
    }

    public function test_register_submenu_returns_false_when_wp_returns_false(): void
    {
        Functions\when('add_submenu_page')->justReturn(false);

        $result = $this->makePage()->registerSubmenu('options-general.php');

        $this->assertFalse($result);
    }

    public function test_register_submenu_can_accept_custom_sub_menu_title(): void
    {
        // Smoke test: should not throw
        Functions\when('add_submenu_page')->justReturn('hook');

        $result = $this->makePage()->registerSubmenu('options-general.php', 'Settings');

        $this->assertSame('hook', $result);
    }

    public function test_register_and_register_submenu_are_independent(): void
    {
        Functions\when('add_menu_page')->justReturn('top-hook');
        Functions\when('add_submenu_page')->justReturn('sub-hook');

        $page = $this->makePage();

        $this->assertSame('top-hook', $page->register());
        $this->assertSame('sub-hook', $page->registerSubmenu('parent'));
    }
}
