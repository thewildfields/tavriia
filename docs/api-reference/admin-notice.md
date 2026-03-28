---
title: AdminNotice
description: API reference for TheWildFields\Tavriia\Admin\AdminNotice
class: AdminNotice
namespace: TheWildFields\Tavriia\Admin
type: final-class
sidebar_position: 14
---

# AdminNotice

```
TheWildFields\Tavriia\Admin\AdminNotice
```

Creates and displays WordPress admin notices. Wraps the `admin_notices` hook.

---

## Class Signature

```php
final class AdminNotice
```

---

## Constants

| Constant | Value | Visual Style |
|----------|-------|-------------|
| `AdminNotice::TYPE_SUCCESS` | `'success'` | Green border |
| `AdminNotice::TYPE_ERROR` | `'error'` | Red border |
| `AdminNotice::TYPE_WARNING` | `'warning'` | Yellow/orange border |
| `AdminNotice::TYPE_INFO` | `'info'` | Blue border |

---

## Named Constructors

Use these static factories instead of the constructor directly.

### `success(string $message, bool $isDismissible = true): self`

Creates a success notice.

```php
public static function success(string $message, bool $isDismissible = true): self
```

---

### `error(string $message, bool $isDismissible = true): self`

Creates an error notice.

```php
public static function error(string $message, bool $isDismissible = true): self
```

---

### `warning(string $message, bool $isDismissible = true): self`

Creates a warning notice.

```php
public static function warning(string $message, bool $isDismissible = true): self
```

---

### `info(string $message, bool $isDismissible = true): self`

Creates an informational notice.

```php
public static function info(string $message, bool $isDismissible = true): self
```

---

## Instance Methods

### `enqueue(): void`

Registers the notice for display by hooking into `admin_notices`.

```php
public function enqueue(): void
```

Call before the `admin_notices` hook fires (e.g. in `admin_init`).

```php
add_action('admin_init', function () {
    AdminNotice::success('Settings saved successfully.')->enqueue();
});
```

---

### `render(): void`

Outputs the notice HTML immediately.

```php
public function render(): void
```

Use this inside an `admin_notices` callback when you want direct control:

```php
add_action('admin_notices', function () {
    AdminNotice::warning('Sync has not run in 7 days.')->render();
});
```

---

### `message(): string`

Returns the notice message text.

```php
public function message(): string
```

---

### `type(): string`

Returns the notice type string (one of the `TYPE_*` constants).

```php
public function type(): string
```

---

### `isDismissible(): bool`

Returns whether the notice includes a dismiss button.

```php
public function isDismissible(): bool
```

---

## Examples

### Non-dismissible error

```php
AdminNotice::error(
    'API key is missing. Events sync is disabled.',
    isDismissible: false,
)->enqueue();
```

### Transient-based notice (survives redirect)

```php
// After handling a form POST:
set_transient('my_plugin_notice', [
    'type'    => 'success',
    'message' => 'Event imported successfully.',
], 60);
wp_safe_redirect(admin_url('admin.php?page=events-manager'));
exit;

// In admin_notices:
add_action('admin_notices', function () {
    $data = get_transient('my_plugin_notice');
    if ($data) {
        delete_transient('my_plugin_notice');
        AdminNotice::{$data['type']}($data['message'])->render();
    }
});
```

---

## See Also

- [Admin Helpers guide](../admin.md)
- [`AdminMenuPage`](admin-menu-page.md)
