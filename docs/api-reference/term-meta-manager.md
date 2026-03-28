---
title: TermMetaManager
description: API reference for TheWildFields\Tavriia\Taxonomy\TermMetaManager
class: TermMetaManager
namespace: TheWildFields\Tavriia\Taxonomy
type: final-class
extends: AbstractMetaManager
sidebar_position: 7
---

# TermMetaManager

```
TheWildFields\Tavriia\Taxonomy\TermMetaManager
```

Typed accessor and mutator for WordPress term meta. Extends `AbstractMetaManager` (which provides all typed getters). Obtain a `TermMetaManager` via `TermRepository::metaFor()`.

---

## Class Signature

```php
final class TermMetaManager extends AbstractMetaManager
```

### Constructor

```php
public function __construct(int $termId)
```

Typically not instantiated directly — use `TermRepository::metaFor($termId)`.

---

## Inherited Typed Getters

All typed getters are inherited from `AbstractMetaManager`. See [`MetaManager`](meta-manager.md#typed-getters) for full documentation.

| Method | Returns | Default |
|--------|---------|---------|
| `getString(string $key, string $default = '')` | `string` | `''` |
| `getInt(string $key, int $default = 0)` | `int` | `0` |
| `getFloat(string $key, float $default = 0.0)` | `float` | `0.0` |
| `getBool(string $key, bool $default = false)` | `bool` | `false` |
| `getArray(string $key, array $default = [])` | `array` | `[]` |

**Example:**
```php
$meta = $repository->metaFor($termId);

$color    = $meta->getString('color_hex', '#000000');
$order    = $meta->getInt('display_order');
$featured = $meta->getBool('is_featured');
$aliases  = $meta->getArray('name_aliases');
```

---

## Methods

### `set(string $key, mixed $value): bool`

Writes a term meta value.

```php
public function set(string $key, mixed $value): bool
```

Wraps `update_term_meta()`. Returns `true` on success.

```php
$meta->set('color_hex', '#FF5733');
$meta->set('is_featured', true);
```

---

### `delete(string $key, mixed $value = ''): bool`

Deletes a term meta entry.

```php
public function delete(string $key, mixed $value = ''): bool
```

Wraps `delete_term_meta()`.

```php
$meta->delete('deprecated_field');
```

---

### `has(string $key): bool`

Checks whether a meta key exists for this term.

```php
public function has(string $key): bool
```

Wraps `metadata_exists('term', ...)`.

```php
if ($meta->has('color_hex')) {
    $color = $meta->getString('color_hex');
}
```

---

### `all(): array`

Returns all term meta as a flat `key → value` array.

```php
public function all(): array
```

Returns `[]` if no meta exists.

```php
$allMeta = $meta->all();
// ['color_hex' => '#4A90E2', 'is_featured' => '1', ...]
```

---

## See Also

- [Taxonomy & Terms guide](../taxonomy.md)
- [`MetaManager`](meta-manager.md) — post meta equivalent
- [`AbstractMetaManager`](meta-manager.md#abstractmetamanager)
- [`TermRepository`](term-repository.md)
