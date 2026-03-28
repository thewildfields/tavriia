---
title: QueryArgsDTO
description: API reference for TheWildFields\Tavriia\DTO\QueryArgsDTO
class: QueryArgsDTO
namespace: TheWildFields\Tavriia\DTO
type: final-readonly-class
sidebar_position: 24
---

# QueryArgsDTO

```
TheWildFields\Tavriia\DTO\QueryArgsDTO
```

Immutable data transfer object representing WordPress query arguments. Used as input to `PostRepository::findMany()` and produced by `QueryBuilder::toDto()`.

Prefer building `QueryArgsDTO` through `QueryBuilder` rather than constructing it directly.

---

## Class Signature

```php
final readonly class QueryArgsDTO
```

---

## Constructor

```php
public function __construct(
    public string|array $postType     = 'post',
    public string       $postStatus   = 'publish',
    public int          $postsPerPage = 10,
    public int          $paged        = 1,
    public string       $orderBy      = 'date',
    public string       $order        = 'DESC',
    public int          $authorId     = 0,
    public int          $parentId     = 0,
    public array        $postIn       = [],
    public array        $postNotIn    = [],
    public array        $metaQuery    = [],
    public array        $taxQuery     = [],
    public string       $search       = '',
    public array        $extra        = [],
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$postType` | `string\|array` | `'post'` | Post type slug or array of slugs |
| `$postStatus` | `string` | `'publish'` | Post status |
| `$postsPerPage` | `int` | `10` | Posts per page. `-1` for all |
| `$paged` | `int` | `1` | Current page number |
| `$orderBy` | `string` | `'date'` | Field to order by |
| `$order` | `string` | `'DESC'` | Sort direction: `'ASC'` or `'DESC'` |
| `$authorId` | `int` | `0` | Filter by author ID. `0` = no filter |
| `$parentId` | `int` | `0` | Filter by parent post ID. `0` = no filter |
| `$postIn` | `array` | `[]` | Restrict to these post IDs |
| `$postNotIn` | `array` | `[]` | Exclude these post IDs |
| `$metaQuery` | `array` | `[]` | Raw WP_Query `meta_query` clauses |
| `$taxQuery` | `array` | `[]` | Raw WP_Query `tax_query` clauses |
| `$search` | `string` | `''` | Keyword search string |
| `$extra` | `array` | `[]` | Additional raw WP_Query args |

---

## Methods

### `toWpQueryArgs(): array`

Converts the DTO to a raw WP_Query arguments array.

```php
public function toWpQueryArgs(): array
```

**Returns:** `array` — ready to pass to `new \WP_Query($args)` or `get_posts($args)`.

Fields with zero/empty values that have no semantic meaning (e.g. `authorId = 0`) are omitted from the output.

---

## Usage

### Direct construction

For use with `PostRepository::findMany()` when you need a simple query:

```php
use TheWildFields\Tavriia\DTO\QueryArgsDTO;

$args = new QueryArgsDTO(
    postType:     'event',
    postStatus:   'publish',
    postsPerPage: 20,
    orderBy:      'title',
    order:        'ASC',
);

$posts = $repository->findMany($args);
```

### Via QueryBuilder (preferred)

```php
use TheWildFields\Tavriia\Query\QueryBuilder;

$dto = (new QueryBuilder())
    ->postType('event')
    ->status('publish')
    ->limit(20)
    ->orderBy('title', 'ASC')
    ->toDto(); // returns QueryArgsDTO
```

---

## See Also

- [Query Builder guide](../query-builder.md)
- [`QueryBuilder`](query-builder.md)
- [`PostRepository`](post-repository.md)
