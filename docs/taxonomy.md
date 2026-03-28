---
title: Taxonomy & Terms
description: Create, update, delete, and query WordPress taxonomy terms with type safety
sidebar_position: 5
---

# Taxonomy & Terms

Tavriia provides three classes for working with WordPress taxonomy terms:

| Class | Responsibility |
|-------|---------------|
| `TermFactory` | Create, update, delete terms; assign terms to posts |
| `TermRepository` | Read terms by ID, slug, or object |
| `TermMetaManager` | Read and write term meta with typed accessors |

---

## TaxonomyDTO

All term data is represented as a `TaxonomyDTO`:

```php
use TheWildFields\Tavriia\DTO\TaxonomyDTO;

$dto = new TaxonomyDTO(
    name: 'Jazz Festival',
    taxonomy: 'event_genre',
    slug: 'jazz-festival',
    description: 'Jazz and blues events',
    parentId: 0,
    meta: [
        'color_hex' => '#4A90E2',
        'featured'  => true,
    ],
);
```

`TaxonomyDTO` can also be built from a `WP_Term` object:

```php
$dto = TaxonomyDTO::fromWpTerm($wpTerm);
```

---

## TermFactory

### Creating a Term

```php
use TheWildFields\Tavriia\Taxonomy\TermFactory;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

$factory = new TermFactory();

try {
    $termId = $factory->create($dto);
} catch (TermNotFoundException $e) {
    // WordPress rejected the insert
}
```

`create()` inserts the term and writes any `$dto->meta` entries. Returns the new term ID as `int`.

### Updating a Term

```php
$updatedDto = new TaxonomyDTO(
    name: 'Jazz & Blues Festival',
    taxonomy: 'event_genre',
    slug: 'jazz-blues-festival',
    description: 'Jazz, blues, and soul events',
);

try {
    $termId = $factory->update($existingTermId, $updatedDto);
} catch (TermNotFoundException $e) {
    // Term not found or update failed
}
```

### Deleting a Term

```php
try {
    $factory->delete($termId, 'event_genre');
} catch (TermNotFoundException $e) {
    // Term does not exist
}
```

### Assigning Terms to a Post

```php
// Replace all terms on a post
$factory->setObjectTerms(
    objectId: $postId,
    taxonomy: 'event_genre',
    terms: [12, 34, 56],         // term IDs or slugs
);

// Append terms without removing existing ones
$factory->setObjectTerms(
    objectId: $postId,
    taxonomy: 'event_genre',
    terms: [78],
    append: true,
);

// Remove all terms from a post
$factory->removeObjectTerms($postId, 'event_genre');
```

---

## TermRepository

### Find by ID

```php
use TheWildFields\Tavriia\Taxonomy\TermRepository;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

$repository = new TermRepository();

try {
    $term = $repository->findById($termId, 'event_genre');
    echo $term->name;
    echo $term->slug;
    echo $term->count; // number of posts with this term
} catch (TermNotFoundException $e) {
    // No term with this ID in this taxonomy
}
```

### Find by Field

```php
// By slug
try {
    $term = $repository->findBy('slug', 'jazz-festival', 'event_genre');
} catch (TermNotFoundException $e) { }

// By name
try {
    $term = $repository->findBy('name', 'Jazz Festival', 'event_genre');
} catch (TermNotFoundException $e) { }
```

### Find All Terms in a Taxonomy

```php
$genres = $repository->findAll('event_genre');              // TaxonomyDTO[]
$topLevel = $repository->findAll('event_genre', ['parent' => 0]);
$ordered  = $repository->findAll('event_genre', ['orderby' => 'count', 'order' => 'DESC']);
```

`findAll()` accepts any standard `get_terms()` args as the second parameter. Returns `[]` on error or when no terms exist.

### Find Terms on a Post

```php
$terms = $repository->findByObject($postId, 'event_genre'); // TaxonomyDTO[]

foreach ($terms as $term) {
    echo $term->name . "\n";
}
```

Throws `TermNotFoundException` if WordPress returns a `WP_Error` (e.g. invalid taxonomy). Returns `[]` if the post has no terms.

### Check Existence

```php
if ($repository->exists($termId, 'event_genre')) {
    // term exists
}
```

### Get Meta Manager

```php
$meta = $repository->metaFor($termId);
// Returns TermMetaManager without fetching the term
```

---

## TermMetaManager

Typed accessors for term meta. Obtain a `TermMetaManager` via `TermRepository::metaFor()`.

```php
$meta = $repository->metaFor($termId);

$color    = $meta->getString('color_hex');           // string
$order    = $meta->getInt('display_order');           // int
$featured = $meta->getBool('is_featured');            // bool
$aliases  = $meta->getArray('name_aliases');          // array
```

All typed getters accept an optional default:

```php
$color = $meta->getString('color_hex', '#000000');
```

### Writing Meta

```php
$meta->set('color_hex', '#FF5733');
$meta->set('is_featured', true);
```

### Deleting Meta

```php
$meta->delete('deprecated_field');
```

### Checking Existence

```php
if ($meta->has('color_hex')) {
    $color = $meta->getString('color_hex');
}
```

### Reading All Meta

```php
$allMeta = $meta->all(); // array<string, mixed>
```

---

## Error Handling Summary

| Operation | Throws |
|-----------|--------|
| `create()` | `TermNotFoundException` if insert fails |
| `update()` | `TermNotFoundException` if term not found or update fails |
| `delete()` | `TermNotFoundException` if term not found |
| `setObjectTerms()` | `TermNotFoundException` if WordPress returns WP_Error |
| `findById()` | `TermNotFoundException` if no term with that ID |
| `findBy()` | `TermNotFoundException` if no matching term |
| `findByObject()` | `TermNotFoundException` if WordPress returns WP_Error |

`findAll()` and `exists()` never throw.

---

## API Reference

- [`TermFactory`](api-reference/term-factory.md)
- [`TermRepository`](api-reference/term-repository.md)
- [`TermMetaManager`](api-reference/term-meta-manager.md)
- [`TaxonomyDTO`](api-reference/taxonomy-dto.md)
- [`TermNotFoundException`](api-reference/exceptions.md#termnotfoundexception)
