---
title: AdminMenuPage
description: API reference for TheWildFields\Tavriia\Admin\AdminMenuPage
class: AdminMenuPage
namespace: TheWildFields\Tavriia\Admin
type: final-class
sidebar_position: 13
---

# AdminMenuPage

```
TheWildFields\Tavriia\Admin\AdminMenuPage
```

Registers WordPress admin menu pages and subpages. Wraps `add_menu_page()` and `add_submenu_page()`.

---

## Class Signature

```php
final class AdminMenuPage
```

---

## Constructor

```php
public function __construct(
    string          $pageTitle,
    string          $menuTitle,
    string          $capability,
    string          $menuSlug,
    callable        $callback,
    string          $iconUrl  = '',
    int|float|null  $position = null,
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$pageTitle` | `string` | — | Text displayed in the browser title bar |
| `$menuTitle` | `string` | — | Text displayed in the admin sidebar |
| `$capability` | `string` | — | WordPress capability required to access the page (e.g. `'manage_options'`) |
| `$menuSlug` | `string` | — | Unique slug for this page (used in URL: `admin.php?page=<slug>`) |
| `$callback` | `callable` | — | Function that renders the page HTML |
| `$iconUrl` | `string` | `''` | Dashicons class (e.g. `'dashicons-calendar-alt'`) or URL to an icon image |
| `$position` | `int\|float\|null` | `null` | Menu position in the sidebar. `null` appends to the end |

---

## Methods

### `register(): string`

Registers the page as a top-level admin menu item.

```php
public function register(): string
```

**Returns:** `string` — the hook suffix, suitable for use with `add_action("load-{$suffix}")`.

Must be called inside an `admin_menu` action callback.

**Example:**
```php
use TheWildFields\Tavriia\Admin\AdminMenuPage;

$page = new AdminMenuPage(
    pageTitle:  'Events Manager',
    menuTitle:  'Events',
    capability: 'manage_options',
    menuSlug:   'events-manager',
    callback:   [$this, 'render'],
    iconUrl:    'dashicons-calendar-alt',
    position:   30,
);

add_action('admin_menu', function () use ($page) {
    $suffix = $page->register();

    // Enqueue scripts only on this page
    add_action("load-{$suffix}", function () {
        wp_enqueue_script('events-admin');
    });
});
```

---

### `registerSubmenu(string $parentSlug, string $subMenuTitle = ''): string|false`

Registers the page as a submenu item under an existing menu.

```php
public function registerSubmenu(string $parentSlug, string $subMenuTitle = ''): string|false
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$parentSlug` | `string` | — | Slug of the parent menu page |
| `$subMenuTitle` | `string` | `''` | Override for the submenu link text. Defaults to `$pageTitle` |

**Returns:** `string|false` — hook suffix on success, `false` on failure.

**Example:**
```php
$importPage = new AdminMenuPage(
    pageTitle:  'Import Events',
    menuTitle:  'Import',
    capability: 'manage_options',
    menuSlug:   'events-import',
    callback:   [$this, 'renderImport'],
);

add_action('admin_menu', function () use ($importPage) {
    $importPage->registerSubmenu('events-manager');
});
```

---

## See Also

- [Admin Helpers guide](../admin.md)
- [`AdminNotice`](admin-notice.md)
