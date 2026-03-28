---
title: QueryResult
description: API reference for TheWildFields\Tavriia\Query\QueryResult
class: QueryResult
namespace: TheWildFields\Tavriia\Query
type: final-class
implements: "Countable, IteratorAggregate"
sidebar_position: 9
---

# QueryResult

```
TheWildFields\Tavriia\Query\QueryResult
```

Immutable, typed wrapper around `WP_Query` results. Returned by `QueryBuilder::get()`. Implements `Countable` and `IteratorAggregate`.

---

## Class Signature

```php
final class QueryResult implements \Countable, \IteratorAggregate
```

---

## Construction

`QueryResult` is typically not constructed manually — `QueryBuilder::get()` creates it internally.

To build one from an existing `WP_Query` instance:

```php
$wpQuery = new \WP_Query($args);
$result = QueryResult::fromWpQuery($wpQuery);
```

### `fromWpQuery(\WP_Query $query): self`

Static factory. Converts `WP_Query` results to `PostDTO[]` and captures pagination metadata.

---

## Methods

### `posts(): PostDTO[]`

Returns all posts in the current page of results.

```php
public function posts(): PostDTO[]
```

```php
$posts = $results->posts();
foreach ($posts as $post) {
    echo $post->title;
}
```

---

### `first(): ?PostDTO`

Returns the first post, or `null` if the result set is empty.

```php
public function first(): ?PostDTO
```

```php
$latest = $results->first();
if ($latest !== null) {
    echo $latest->title;
}
```

---

### `isEmpty(): bool`

Returns `true` if there are no posts in the current page.

```php
public function isEmpty(): bool
```

```php
if ($results->isEmpty()) {
    echo 'No events found.';
}
```

---

### `totalPosts(): int`

Returns the total number of posts matching the query across all pages.

```php
public function totalPosts(): int
```

```php
echo "Found {$results->totalPosts()} events total";
```

---

### `totalPages(): int`

Returns the total number of pages.

```php
public function totalPages(): int
```

---

### `currentPage(): int`

Returns the current page number.

```php
public function currentPage(): int
```

---

### `hasNextPage(): bool`

Returns `true` if there is a next page of results.

```php
public function hasNextPage(): bool
```

```php
if ($results->hasNextPage()) {
    $nextPage = $results->currentPage() + 1;
}
```

---

### `count(): int`

Returns the number of posts on the current page. Implements `Countable`.

```php
public function count(): int
```

```php
echo count($results); // posts on this page
```

---

### `getIterator(): \ArrayIterator`

Returns an iterator over the current page's posts. Implements `IteratorAggregate`.

```php
public function getIterator(): \ArrayIterator
```

Enables `foreach` directly on the result:

```php
foreach ($results as $post) {
    echo $post->title . "\n";
}
```

---

## Pagination Example

```php
$page = (int) ($_GET['paged'] ?? 1);

$results = (new QueryBuilder())
    ->postType('event')
    ->limit(12)
    ->page($page)
    ->get();

echo "Showing page {$results->currentPage()} of {$results->totalPages()}";
echo " ({$results->totalPosts()} total)";

foreach ($results as $post) {
    echo $post->title . "\n";
}

if ($results->hasNextPage()) {
    $next = $results->currentPage() + 1;
    echo "<a href='?paged={$next}'>Next page</a>";
}
```

---

## See Also

- [Query Builder guide](../query-builder.md)
- [`QueryBuilder`](query-builder.md)
- [`PostDTO`](post-dto.md)
