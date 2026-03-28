---
title: MetaManager & AbstractMetaManager
description: API reference for TheWildFields\Tavriia\Post\MetaManager and AbstractMetaManager
class: MetaManager
namespace: TheWildFields\Tavriia\Post
type: final-class
extends: AbstractMetaManager
sidebar_position: 4
---

# MetaManager

```
TheWildFields\Tavriia\Post\MetaManager
TheWildFields\Tavriia\Post\AbstractMetaManager
```

Typed accessor and mutator for WordPress post meta. `MetaManager` extends `AbstractMetaManager`, which provides all typed getter methods. Obtain a `MetaManager` via `PostRepository::metaFor()`.

---

## AbstractMetaManager

```
TheWildFields\Tavriia\Post\AbstractMetaManager
```

Abstract base class shared by `MetaManager` (post meta) and `TermMetaManager` (term meta). Defines the typed getter interface.

### Abstract Method

#### `getRawValue(string $key): mixed`

Subclasses implement this to fetch the raw meta value from WordPress.

---

### Typed Getters

All typed getters are defined on `AbstractMetaManager` and available on both `MetaManager` and `TermMetaManager`.

#### `getString(string $key, string $default = ''): string`

Returns the meta value cast to `string`. Returns `$default` if the value is absent, `null`, `false`, or an empty string.

```php
$name = $meta->getString('venue_name');
$name = $meta->getString('venue_name', 'Unknown Venue');
```

---

#### `getInt(string $key, int $default = 0): int`

Returns the meta value cast to `int`. Returns `$default` if absent or empty.

```php
$capacity = $meta->getInt('capacity');
$capacity = $meta->getInt('capacity', 100);
```

---

#### `getFloat(string $key, float $default = 0.0): float`

Returns the meta value cast to `float`. Returns `$default` if absent or empty.

```php
$price = $meta->getFloat('ticket_price');
$price = $meta->getFloat('ticket_price', 9.99);
```

---

#### `getBool(string $key, bool $default = false): bool`

Returns the meta value interpreted as `bool`. Returns `$default` if absent or empty.

**Truthy values** (case-insensitive): `1`, `'1'`, `'true'`, `'yes'`, `'on'`. All other values return `false`.

```php
$isOpen     = $meta->getBool('is_open');
$isFeatured = $meta->getBool('is_featured', false);
```

---

#### `getArray(string $key, array $default = []): array`

Returns the meta value as an `array`. If the stored value is not already an array, wraps it in one. Returns `$default` if absent or empty.

```php
$tags  = $meta->getArray('tags');
$items = $meta->getArray('line_items', []);
```

---

## MetaManager

```
TheWildFields\Tavriia\Post\MetaManager
```

Bound to a specific post ID. Provides typed reads plus write and delete operations.

### Constructor

```php
public function __construct(int $postId)
```

Typically not instantiated directly — use `PostRepository::metaFor($postId)`.

---

### Read Methods

All typed getters are inherited from `AbstractMetaManager`. See above.

---

### `set(string $key, mixed $value): bool`

Writes a post meta value.

```php
public function set(string $key, mixed $value): bool
```

Wraps `update_post_meta()`. Returns `true` on success, `false` on failure.

```php
$meta->set('venue_id', 42);
$meta->set('tags', ['music', 'outdoor']);
```

---

### `delete(string $key, mixed $value = ''): bool`

Deletes a post meta entry.

```php
public function delete(string $key, mixed $value = ''): bool
```

Wraps `delete_post_meta()`. Pass `$value` to delete only a specific value when multiple exist.

```php
$meta->delete('old_field');
$meta->delete('tags', 'specific-tag');
```

---

### `has(string $key): bool`

Checks whether a meta key exists for this post.

```php
public function has(string $key): bool
```

Wraps `metadata_exists('post', ...)`. Returns `true` even if the stored value is `false` or `0`.

```php
if ($meta->has('venue_id')) {
    $id = $meta->getInt('venue_id');
}
```

---

### `all(): array`

Returns all post meta as a flat `key → value` array.

```php
public function all(): array
```

Each value is the first entry from `get_post_meta($id, '', false)`. Returns an empty array if no meta exists.

```php
$allMeta = $meta->all();
// ['venue_id' => '42', 'start_date' => '2026-07-15', ...]
```

---

## See Also

- [Posts & Meta guide](../posts.md)
- [`PostRepository`](post-repository.md)
- [`TermMetaManager`](term-meta-manager.md)
