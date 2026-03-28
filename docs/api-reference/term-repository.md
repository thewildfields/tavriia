---
title: TermRepository
description: API reference for TheWildFields\Tavriia\Taxonomy\TermRepository
class: TermRepository
namespace: TheWildFields\Tavriia\Taxonomy
type: final-class
implements: TaxonomyRepositoryInterface
sidebar_position: 6
---

# TermRepository

```
TheWildFields\Tavriia\Taxonomy\TermRepository
```

Reads WordPress taxonomy terms and returns typed DTOs. Wraps `get_term()`, `get_term_by()`, `get_terms()`, and `wp_get_object_terms()`. Implements `TaxonomyRepositoryInterface`.

---

## Class Signature

```php
final class TermRepository implements TaxonomyRepositoryInterface
```

No constructor dependencies.

---

## Methods

### `findById(int $id, string $taxonomy): TaxonomyDTO`

Fetches a term by its ID within a specific taxonomy.

```php
public function findById(int $id, string $taxonomy): TaxonomyDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | Term ID |
| `$taxonomy` | `string` | Taxonomy name |

**Returns:** `TaxonomyDTO`

**Throws:** `TermNotFoundException` if no term exists with the given ID in the taxonomy.

**Example:**
```php
try {
    $genre = $repository->findById(12, 'event_genre');
    echo $genre->name;
    echo $genre->slug;
    echo $genre->count;
} catch (TermNotFoundException $e) {
    // term does not exist
}
```

---

### `findBy(string $field, string|int $value, string $taxonomy): TaxonomyDTO`

Fetches a term by a specific field.

```php
public function findBy(string $field, string|int $value, string $taxonomy): TaxonomyDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$field` | `string` | Field to search by: `'id'`, `'slug'`, `'name'`, `'term_taxonomy_id'` |
| `$value` | `string\|int` | Value to match |
| `$taxonomy` | `string` | Taxonomy name |

**Returns:** `TaxonomyDTO`

**Throws:** `TermNotFoundException` if no matching term is found.

**Example:**
```php
// By slug
$genre = $repository->findBy('slug', 'jazz-festival', 'event_genre');

// By name
$genre = $repository->findBy('name', 'Jazz Festival', 'event_genre');
```

---

### `findAll(string $taxonomy, array $args = []): TaxonomyDTO[]`

Fetches all terms in a taxonomy.

```php
public function findAll(string $taxonomy, array $args = []): TaxonomyDTO[]
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$taxonomy` | `string` | Taxonomy name |
| `$args` | `array` | Optional `get_terms()` arguments to merge |

**Returns:** `TaxonomyDTO[]` — always an array. Returns `[]` if no terms exist or on error.

**Never throws.**

**Example:**
```php
// All terms
$genres = $repository->findAll('event_genre');

// Top-level terms only
$topLevel = $repository->findAll('event_genre', ['parent' => 0]);

// Ordered by post count
$popular = $repository->findAll('event_genre', [
    'orderby' => 'count',
    'order'   => 'DESC',
    'number'  => 10,
]);
```

---

### `findByObject(int $objectId, string $taxonomy): TaxonomyDTO[]`

Fetches all terms assigned to a post (or any object).

```php
public function findByObject(int $objectId, string $taxonomy): TaxonomyDTO[]
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$objectId` | `int` | Post ID |
| `$taxonomy` | `string` | Taxonomy name |

**Returns:** `TaxonomyDTO[]` — always an array. Returns `[]` if the post has no terms assigned.

**Throws:** `TermNotFoundException` if WordPress returns a `WP_Error` (e.g. invalid taxonomy).

**Example:**
```php
$genres = $repository->findByObject($postId, 'event_genre');

foreach ($genres as $genre) {
    echo $genre->name . "\n";
}
```

---

### `exists(int $id, string $taxonomy): bool`

Checks whether a term with the given ID exists in the taxonomy.

```php
public function exists(int $id, string $taxonomy): bool
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | Term ID |
| `$taxonomy` | `string` | Taxonomy name |

**Returns:** `bool`

**Never throws.**

**Example:**
```php
if ($repository->exists($termId, 'event_genre')) {
    $factory->update($termId, $dto);
}
```

---

### `metaFor(int $termId): TermMetaManager`

Returns a `TermMetaManager` for the given term ID without fetching the term.

```php
public function metaFor(int $termId): TermMetaManager
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$termId` | `int` | Term ID |

**Returns:** `TermMetaManager`

**Never throws.** Does not verify the term exists.

**Example:**
```php
$meta = $repository->metaFor($termId);
$color = $meta->getString('color_hex', '#000000');
```

---

## See Also

- [Taxonomy & Terms guide](../taxonomy.md)
- [`TermFactory`](term-factory.md)
- [`TermMetaManager`](term-meta-manager.md)
- [`TaxonomyDTO`](taxonomy-dto.md)
- [`TaxonomyRepositoryInterface`](contracts.md#taxonomyrepositoryinterface)
- [`TermNotFoundException`](exceptions.md#termnotfoundexception)
