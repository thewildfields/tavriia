---
title: TaxonomyDTO
description: API reference for TheWildFields\Tavriia\DTO\TaxonomyDTO
class: TaxonomyDTO
namespace: TheWildFields\Tavriia\DTO
type: final-readonly-class
sidebar_position: 21
---

# TaxonomyDTO

```
TheWildFields\Tavriia\DTO\TaxonomyDTO
```

Immutable data transfer object representing a WordPress taxonomy term. Used as input to `TermFactory` methods and returned by `TermRepository` methods.

---

## Class Signature

```php
final readonly class TaxonomyDTO
```

---

## Constructor

```php
public function __construct(
    public string $name,
    public string $taxonomy,
    public string $slug        = '',
    public string $description = '',
    public int    $parentId    = 0,
    public int    $id          = 0,
    public int    $count       = 0,
    public array  $meta        = [],
)
```

**Required parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$name` | `string` | Term name (display name) |
| `$taxonomy` | `string` | Taxonomy slug this term belongs to |

**Optional parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$slug` | `string` | `''` | URL slug. WordPress generates one from name if empty |
| `$description` | `string` | `''` | Term description |
| `$parentId` | `int` | `0` | Parent term ID for hierarchical taxonomies. `0` = top-level |
| `$id` | `int` | `0` | Term ID. `0` = not yet saved |
| `$count` | `int` | `0` | Number of posts with this term (read-only, set by `fromWpTerm`) |
| `$meta` | `array` | `[]` | Key → value pairs to write via `update_term_meta()` after save |

---

## Static Factory Methods

### `fromWpTerm(\WP_Term $term): self`

Constructs a `TaxonomyDTO` from a WordPress `WP_Term` object.

```php
public static function fromWpTerm(\WP_Term $term): self
```

Used internally by `TermRepository`. Useful when you have a `WP_Term` and need a typed DTO.

```php
$wpTerm = get_term(12, 'event_genre');
if ($wpTerm instanceof \WP_Term) {
    $dto = TaxonomyDTO::fromWpTerm($wpTerm);
}
```

---

### `withId(int $id): self`

Returns a new `TaxonomyDTO` identical to the current one but with the given `$id`.

```php
public function withId(int $id): self
```

---

## Examples

### New top-level term

```php
$dto = new TaxonomyDTO(
    name:     'Jazz',
    taxonomy: 'event_genre',
);
```

### New child term with meta

```php
$dto = new TaxonomyDTO(
    name:        'Contemporary Jazz',
    taxonomy:    'event_genre',
    slug:        'contemporary-jazz',
    description: 'Modern jazz styles and fusion',
    parentId:    12,
    meta: [
        'color_hex'  => '#7B68EE',
        'is_visible' => true,
    ],
);
```

---

## See Also

- [Taxonomy & Terms guide](../taxonomy.md)
- [`TermFactory`](term-factory.md)
- [`TermRepository`](term-repository.md)
