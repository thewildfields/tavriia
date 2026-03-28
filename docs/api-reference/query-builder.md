---
title: QueryBuilder
description: API reference for TheWildFields\Tavriia\Query\QueryBuilder
class: QueryBuilder
namespace: TheWildFields\Tavriia\Query
type: final-class
implements: QueryBuilderInterface
sidebar_position: 8
---

# QueryBuilder

```
TheWildFields\Tavriia\Query\QueryBuilder
```

Fluent, immutable builder for WordPress post queries. Composes `WP_Query` arguments and executes them, returning a typed `QueryResult`. Implements `QueryBuilderInterface`.

---

## Class Signature

```php
final class QueryBuilder implements QueryBuilderInterface
```

No constructor dependencies.

---

## Immutability

Every method returns a **clone** of the builder with the given change applied. The original instance is never modified. This makes it safe to reuse a partially-configured builder as a base for multiple queries.

---

## Methods

### `postType(string|array $postType): static`

Sets the post type filter.

```php
public function postType(string|array $postType): static
```

```php
$builder->postType('event');
$builder->postType(['event', 'webinar']);
```

---

### `status(string $status): static`

Sets the post status filter.

```php
public function status(string $status): static
```

```php
$builder->status('publish');
$builder->status('draft');
$builder->status('any');
```

Default: `'publish'`

---

### `metaQuery(string $key, mixed $value, string $compare = '='): static`

Adds a meta query clause.

```php
public function metaQuery(string $key, mixed $value, string $compare = '='): static
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$key` | `string` | — | Meta key |
| `$value` | `mixed` | — | Value to compare against |
| `$compare` | `string` | `'='` | Comparison operator |

Supported operators: `=`, `!=`, `>`, `>=`, `<`, `<=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`, `BETWEEN`, `NOT BETWEEN`, `EXISTS`, `NOT EXISTS`.

Multiple calls accumulate as `AND` clauses.

```php
$builder
    ->metaQuery('venue_id', 42)
    ->metaQuery('start_date', '2026-01-01', '>=')
    ->metaQuery('ticket_price', 50, '<=');
```

---

### `metaExists(string $key): static`

Adds a meta query clause checking for key existence.

```php
public function metaExists(string $key): static
```

Equivalent to `metaQuery($key, '', 'EXISTS')`.

```php
$builder->metaExists('featured_image');
```

---

### `taxQuery(string $taxonomy, array|int|string $terms, string $field = 'term_id', string $operator = 'IN'): static`

Adds a taxonomy query clause.

```php
public function taxQuery(
    string           $taxonomy,
    array|int|string $terms,
    string           $field    = 'term_id',
    string           $operator = 'IN',
): static
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$taxonomy` | `string` | — | Taxonomy name |
| `$terms` | `array\|int\|string` | — | Term IDs, slugs, or names |
| `$field` | `string` | `'term_id'` | Field to match: `'term_id'`, `'slug'`, `'name'` |
| `$operator` | `string` | `'IN'` | `'IN'`, `'NOT IN'`, `'AND'`, `'EXISTS'`, `'NOT EXISTS'` |

Multiple calls accumulate as `AND` relations.

```php
$builder->taxQuery('event_genre', 12);
$builder->taxQuery('event_genre', 'jazz-festival', 'slug');
$builder->taxQuery('event_genre', [12, 34], 'term_id', 'AND');
```

---

### `orderBy(string $orderBy, string $order = 'DESC'): static`

Sets the sort order.

```php
public function orderBy(string $orderBy, string $order = 'DESC'): static
```

```php
$builder->orderBy('date', 'DESC');
$builder->orderBy('title', 'ASC');
$builder->orderBy('menu_order', 'ASC');
$builder->orderBy('meta_value_num', 'ASC');
```

---

### `limit(int $limit): static`

Sets the number of posts per page.

```php
public function limit(int $limit): static
```

Pass `-1` for all posts. Default: `10`.

```php
$builder->limit(20);
$builder->limit(-1); // all posts
```

---

### `page(int $page): static`

Sets the current page number for pagination. Minimum value: 1.

```php
public function page(int $page): static
```

```php
$builder->page((int) ($_GET['paged'] ?? 1));
```

---

### `author(int $authorId): static`

Filters posts by author ID.

```php
public function author(int $authorId): static
```

```php
$builder->author(5);
```

---

### `parent(int $parentId): static`

Filters posts by parent post ID.

```php
public function parent(int $parentId): static
```

```php
$builder->parent(100); // child posts of post 100
```

---

### `whereIn(array $ids): static`

Restricts results to specific post IDs.

```php
public function whereIn(array $ids): static
```

```php
$builder->whereIn([10, 20, 30]);
```

---

### `whereNotIn(array $ids): static`

Excludes specific post IDs from results.

```php
public function whereNotIn(array $ids): static
```

```php
$builder->whereNotIn([99, 100]);
```

---

### `search(string $query): static`

Sets a keyword search (WordPress `s` parameter).

```php
public function search(string $query): static
```

```php
$builder->search('jazz festival');
```

---

### `withArgs(array $args): static`

Merges additional `WP_Query` arguments not covered by the fluent API.

```php
public function withArgs(array $args): static
```

```php
$builder->withArgs([
    'fields'     => 'ids',
    'meta_key'   => 'start_date',
    'date_query' => [['after' => '1 week ago']],
]);
```

---

### `toDto(): QueryArgsDTO`

Returns the current builder state as a `QueryArgsDTO` without executing the query.

```php
public function toDto(): QueryArgsDTO
```

---

### `get(): QueryResult`

Executes the query and returns a typed `QueryResult`.

```php
public function get(): QueryResult
```

**Returns:** `QueryResult`

**Never throws.**

```php
$results = $builder->get();

foreach ($results as $post) {
    echo $post->title;
}
```

---

## See Also

- [Query Builder guide](../query-builder.md)
- [`QueryResult`](query-result.md)
- [`QueryArgsDTO`](query-args-dto.md)
- [`QueryBuilderInterface`](contracts.md#querybuilderinterface)
