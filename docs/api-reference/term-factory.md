---
title: TermFactory
description: API reference for TheWildFields\Tavriia\Taxonomy\TermFactory
class: TermFactory
namespace: TheWildFields\Tavriia\Taxonomy
type: final-class
sidebar_position: 5
---

# TermFactory

```
TheWildFields\Tavriia\Taxonomy\TermFactory
```

Creates, updates, and deletes WordPress taxonomy terms, and assigns terms to posts. Wraps `wp_insert_term()`, `wp_update_term()`, `wp_delete_term()`, and `wp_set_object_terms()`.

---

## Class Signature

```php
final class TermFactory
```

No constructor dependencies.

---

## Methods

### `create(TaxonomyDTO $dto): int`

Inserts a new term into a taxonomy.

```php
public function create(TaxonomyDTO $dto): int
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$dto` | `TaxonomyDTO` | Term data to insert |

**Returns:** `int` — the ID of the newly created term.

**Throws:** `TermNotFoundException` if WordPress rejects the insert.

**Behavior:**
- Calls `wp_insert_term($dto->name, $dto->taxonomy, $args)`
- If `$dto->meta` is non-empty, writes each entry via `update_term_meta()`

**Example:**
```php
use TheWildFields\Tavriia\DTO\TaxonomyDTO;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

$dto = new TaxonomyDTO(
    name:        'Jazz Festival',
    taxonomy:    'event_genre',
    slug:        'jazz-festival',
    description: 'Jazz and blues events',
    meta:        ['color_hex' => '#4A90E2'],
);

try {
    $termId = $factory->create($dto);
} catch (TermNotFoundException $e) {
    // handle failure
}
```

---

### `update(int $id, TaxonomyDTO $dto): int`

Updates an existing term.

```php
public function update(int $id, TaxonomyDTO $dto): int
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | ID of the term to update |
| `$dto` | `TaxonomyDTO` | New term data |

**Returns:** `int` — the term ID.

**Throws:** `TermNotFoundException` if the term does not exist or update fails.

**Example:**
```php
$termId = $factory->update($existingId, new TaxonomyDTO(
    name:     'Jazz & Blues',
    taxonomy: 'event_genre',
));
```

---

### `delete(int $id, string $taxonomy): void`

Deletes a term from a taxonomy.

```php
public function delete(int $id, string $taxonomy): void
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | Term ID to delete |
| `$taxonomy` | `string` | Taxonomy the term belongs to |

**Throws:** `TermNotFoundException` if the term does not exist.

**Example:**
```php
$factory->delete($termId, 'event_genre');
```

---

### `setObjectTerms(int $objectId, string $taxonomy, array $terms, bool $append = false): void`

Assigns terms to a post (or any object type).

```php
public function setObjectTerms(
    int    $objectId,
    string $taxonomy,
    array  $terms,
    bool   $append = false,
): void
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$objectId` | `int` | — | Post ID (or object ID) |
| `$taxonomy` | `string` | — | Taxonomy to assign terms in |
| `$terms` | `array` | — | Term IDs or slugs to assign |
| `$append` | `bool` | `false` | If `true`, adds to existing terms; if `false`, replaces them |

**Throws:** `TermNotFoundException` if WordPress returns a `WP_Error`.

**Example:**
```php
// Replace all terms
$factory->setObjectTerms($postId, 'event_genre', [12, 34]);

// Append without removing existing terms
$factory->setObjectTerms($postId, 'event_genre', [56], append: true);
```

---

### `removeObjectTerms(int $objectId, string $taxonomy): void`

Removes all terms from a post in a given taxonomy.

```php
public function removeObjectTerms(int $objectId, string $taxonomy): void
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$objectId` | `int` | Post ID |
| `$taxonomy` | `string` | Taxonomy to clear |

**Throws:** `TermNotFoundException` if WordPress returns a `WP_Error`.

**Example:**
```php
$factory->removeObjectTerms($postId, 'event_genre');
```

---

## See Also

- [Taxonomy & Terms guide](../taxonomy.md)
- [`TermRepository`](term-repository.md)
- [`TaxonomyDTO`](taxonomy-dto.md)
- [`TermNotFoundException`](exceptions.md#termnotfoundexception)
