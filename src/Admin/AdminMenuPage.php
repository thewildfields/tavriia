<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Admin;

/**
 * Typed wrapper around add_menu_page() and add_submenu_page().
 *
 * Register top-level and submenu admin pages without touching
 * WordPress globals directly in plugin code.
 *
 * Usage:
 *   $page = new AdminMenuPage(
 *       pageTitle:  'My Plugin',
 *       menuTitle:  'My Plugin',
 *       capability: 'manage_options',
 *       menuSlug:   'my-plugin',
 *       callback:   [$this, 'renderPage'],
 *       iconUrl:    'dashicons-admin-generic',
 *       position:   80,
 *   );
 *   $page->register();
 */
final class AdminMenuPage
{
    /**
     * @param string          $pageTitle   Text displayed in the browser title bar.
     * @param string          $menuTitle   Text shown in the WordPress admin menu.
     * @param string          $capability  Required user capability to see this menu item.
     * @param string          $menuSlug    Unique slug identifying this menu page.
     * @param callable        $callback    Function that outputs the page HTML.
     * @param string          $iconUrl     URL or dashicons class for the menu icon.
     * @param int|float|null  $position    Position in the admin menu. Null = default.
     */
    public function __construct(
        private readonly string $pageTitle,
        private readonly string $menuTitle,
        private readonly string $capability,
        private readonly string $menuSlug,
        private readonly mixed $callback,
        private readonly string $iconUrl = '',
        private readonly int|float|null $position = null,
    ) {}

    /**
     * Register the top-level menu page with WordPress.
     *
     * Call this inside an add_action('admin_menu', ...) callback.
     *
     * @return string The page's hook suffix (useful for enqueuing scripts/styles).
     */
    public function register(): string
    {
        return (string) add_menu_page(
            page_title: $this->pageTitle,
            menu_title: $this->menuTitle,
            capability: $this->capability,
            menu_slug:  $this->menuSlug,
            callback:   $this->callback,
            icon_url:   $this->iconUrl,
            position:   $this->position,
        );
    }

    /**
     * Register a submenu page under an existing parent menu.
     *
     * Call this inside an add_action('admin_menu', ...) callback.
     *
     * @param string $parentSlug Slug of the parent menu (e.g., 'options-general.php').
     * @param string $subMenuTitle Text shown in the submenu. Defaults to menuTitle.
     *
     * @return string|false The page's hook suffix, or false on failure.
     */
    public function registerSubmenu(string $parentSlug, string $subMenuTitle = ''): string|false
    {
        return add_submenu_page(
            parent_slug: $parentSlug,
            page_title:  $this->pageTitle,
            menu_title:  $subMenuTitle !== '' ? $subMenuTitle : $this->menuTitle,
            capability:  $this->capability,
            menu_slug:   $this->menuSlug,
            callback:    $this->callback,
            position:    $this->position !== null ? (int) $this->position : null,
        );
    }
}
