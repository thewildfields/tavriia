---
title: Admin Helpers
description: Add admin menu pages and display notices in the WordPress admin
sidebar_position: 8
---

# Admin Helpers

Tavriia provides two classes for common WordPress admin tasks:

| Class | Responsibility |
|-------|---------------|
| `AdminMenuPage` | Register admin menu and submenu pages |
| `AdminNotice` | Display admin notices with typed severity levels |

---

## AdminMenuPage

Wraps `add_menu_page()` and `add_submenu_page()`.

### Registering a Top-Level Menu Page

```php
use TheWildFields\Tavriia\Admin\AdminMenuPage;

$page = new AdminMenuPage(
    pageTitle:  'Events Manager',
    menuTitle:  'Events',
    capability: 'manage_options',
    menuSlug:   'events-manager',
    callback:   [$this, 'renderEventsPage'],
    iconUrl:    'dashicons-calendar-alt',
    position:   30,
);

add_action('admin_menu', function () use ($page) {
    $hookSuffix = $page->register();
    // $hookSuffix can be used to enqueue scripts/styles conditionally
});
```

### Registering a Submenu Page

```php
$subPage = new AdminMenuPage(
    pageTitle:  'Import Events',
    menuTitle:  'Import',
    capability: 'manage_options',
    menuSlug:   'events-import',
    callback:   [$this, 'renderImportPage'],
);

add_action('admin_menu', function () use ($subPage) {
    $hookSuffix = $subPage->registerSubmenu(
        parentSlug:   'events-manager',
        subMenuTitle: 'Import Events',  // optional, defaults to pageTitle
    );
});
```

### Using Hook Suffix

The hook suffix returned by `register()` or `registerSubmenu()` can be used to load page-specific assets:

```php
add_action('admin_menu', function () use ($page) {
    $suffix = $page->register();

    add_action("load-{$suffix}", function () {
        wp_enqueue_script('events-admin', plugin_dir_url(__FILE__) . 'events-admin.js');
    });
});
```

### Constructor Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pageTitle` | `string` | Text shown in the browser title bar |
| `$menuTitle` | `string` | Text shown in the admin sidebar menu |
| `$capability` | `string` | WordPress capability required to see the page |
| `$menuSlug` | `string` | Unique slug for this menu item |
| `$callback` | `callable` | Function that renders the page HTML |
| `$iconUrl` | `string` | Dashicons class or URL to icon image. Default: `''` |
| `$position` | `int\|float\|null` | Menu position. Default: `null` (appended) |

---

## AdminNotice

Wraps the `admin_notices` hook to display styled WordPress admin notices.

### Creating Notices

Use the named constructors for typed severity:

```php
use TheWildFields\Tavriia\Admin\AdminNotice;

$success = AdminNotice::success('Events imported successfully.');
$error   = AdminNotice::error('Import failed: could not connect to API.');
$warning = AdminNotice::warning('Some events were skipped due to missing dates.');
$info    = AdminNotice::info('Event sync runs daily at midnight.');
```

All notices are dismissible by default. Pass `false` to disable:

```php
$persistent = AdminNotice::error('Critical error occurred.', isDismissible: false);
```

### Displaying Notices

#### Enqueue (Recommended)

`enqueue()` registers the notice on the `admin_notices` hook. Call it before the hook fires:

```php
add_action('admin_init', function () {
    AdminNotice::success('Settings saved.')->enqueue();
});
```

#### Render Directly

`render()` outputs the HTML immediately. Use inside an `admin_notices` callback:

```php
add_action('admin_notices', function () {
    AdminNotice::warning('Sync is due.')->render();
});
```

### Reading Notice Properties

```php
$notice = AdminNotice::success('Done.');
$notice->message();        // string — 'Done.'
$notice->type();           // string — 'success'
$notice->isDismissible();  // bool — true
```

### Type Constants

| Constant | Value | Visual Style |
|----------|-------|-------------|
| `AdminNotice::TYPE_SUCCESS` | `'success'` | Green border |
| `AdminNotice::TYPE_ERROR` | `'error'` | Red border |
| `AdminNotice::TYPE_WARNING` | `'warning'` | Yellow border |
| `AdminNotice::TYPE_INFO` | `'info'` | Blue border |

### Common Pattern: Transient-Based Notices

Store notices between page loads using WordPress transients:

```php
// After a form submission (POST handler):
set_transient('my_plugin_notice', ['type' => 'success', 'message' => 'Saved!'], 60);
wp_redirect(admin_url('admin.php?page=my-plugin'));

// On the next page load (admin_notices hook):
add_action('admin_notices', function () {
    $notice = get_transient('my_plugin_notice');
    if ($notice) {
        delete_transient('my_plugin_notice');
        AdminNotice::{$notice['type']}($notice['message'])->render();
    }
});
```

---

## API Reference

- [`AdminMenuPage`](api-reference/admin-menu-page.md)
- [`AdminNotice`](api-reference/admin-notice.md)
