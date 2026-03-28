---
title: Query Builder
description: Fluent, type-safe WordPress post queries with QueryBuilder and QueryResult
sidebar_position: 6
---

# Query Builder

`QueryBuilder` is a fluent, immutable builder that composes `WP_Query` arguments and returns a typed `QueryResult`.

---

## Basic Usage

```php
use TheWildFields\Tavriia\Query\QueryBuilder;

$results = (new QueryBuilder())
    ->postType('event')
    ->status('publish')
    ->orderBy('date', 'DESC')
    ->limit(10)
    ->get();
```

---

## Filtering by Post Type

```php
// Single post type
$builder->postType('event');

// Multiple post types
$builder->postType(['event', 'webinar']);
```

---

## Filtering by Status

```php
$builder->status('publish');
$builder->status('draft');
$builder->status('any');
```

---

## Meta Queries

```php
// Exact match (default)
$builder->metaQuery('venue_id', 42);

// With a comparison operator
$builder->metaQuery('start_date', '2026-01-01', '>=');
$builder->metaQuery('capacity', 100, '>');
$builder->metaQuery('status', ['confirmed', 'pending'], 'IN');

// Check key existence
$builder->metaExists('featured_image');
```

Multiple `metaQuery()` calls accumulate as `AND` clauses:

```php
$builder
    ->metaQuery('venue_id', 42)
    ->metaQuery('start_date', '2026-07-01', '>=')
    ->metaQuery('start_date', '2026-07-31', '<=');
```

---

## Taxonomy Queries

```php
// Find posts with a specific term
$builder->taxQuery('event_genre', 12);                         // by term ID
$builder->taxQuery('event_genre', 'jazz-festival', 'slug');   // by slug
$builder->taxQuery('event_genre', [12, 34], 'term_id', 'AND'); // multiple terms (AND)
```

Multiple `taxQuery()` calls accumulate as `AND` relations.

---

## Ordering

```php
$builder->orderBy('date', 'DESC');
$builder->orderBy('title', 'ASC');
$builder->orderBy('menu_order', 'ASC');
$builder->orderBy('meta_value_num', 'ASC'); // when using meta ordering
```

---

## Pagination

```php
$builder->limit(20);       // posts per page
$builder->page(3);         // which page (minimum 1)
```

---

## Author and Parent Filtering

```php
$builder->author(5);         // posts by user ID 5
$builder->parent(100);       // child posts of post ID 100
```

---

## Post ID Filtering

```php
$builder->whereIn([10, 20, 30]);       // only these post IDs
$builder->whereNotIn([99, 100]);       // exclude these post IDs
```

---

## Search

```php
$builder->search('jazz festival');    // wraps WP_Query's 's' parameter
```

---

## Extra WP_Query Args

For any argument not covered by the fluent API:

```php
$builder->withArgs([
    'fields'           => 'ids',
    'suppress_filters' => true,
    'date_query'       => [
        ['after' => '1 week ago'],
    ],
]);
```

`withArgs()` merges deeply with existing builder state.

---

## Immutability

Every setter returns a **clone** of the builder. The original is never modified:

```php
$base = (new QueryBuilder())->postType('event')->status('publish');

// Safe to reuse $base — it is unchanged
$upcoming = $base->metaQuery('start_date', date('Y-m-d'), '>=')->limit(5)->get();
$popular  = $base->orderBy('comment_count', 'DESC')->limit(5)->get();
```

---

## QueryResult

`get()` returns a `QueryResult` — a typed, iterable wrapper around the query output.

### Iterating Posts

```php
$results = $builder->get();

foreach ($results as $post) {
    // $post is a PostDTO
    echo $post->title . "\n";
}
```

### Pagination Metadata

```php
$results->totalPosts();    // int — total across all pages
$results->totalPages();    // int
$results->currentPage();   // int
$results->hasNextPage();   // bool
```

### Accessing Posts Directly

```php
$posts = $results->posts();    // PostDTO[]
$first = $results->first();    // ?PostDTO — null if empty
```

### Checking Emptiness

```php
if ($results->isEmpty()) {
    echo "No results";
}

echo count($results);          // implements Countable
```

---

## Converting to QueryArgsDTO

To inspect or reuse the built query args without executing:

```php
$dto = $builder->toDto(); // QueryArgsDTO
$wpArgs = $dto->toWpQueryArgs(); // array — raw WP_Query args
```

---

## Full Example

```php
use TheWildFields\Tavriia\Query\QueryBuilder;

$page = (int) ($_GET['paged'] ?? 1);

$results = (new QueryBuilder())
    ->postType('event')
    ->status('publish')
    ->taxQuery('event_genre', 'jazz-festival', 'slug')
    ->metaQuery('start_date', date('Y-m-d'), '>=')
    ->orderBy('meta_value', 'ASC')
    ->withArgs(['meta_key' => 'start_date'])
    ->limit(12)
    ->page($page)
    ->get();

echo "Page {$results->currentPage()} of {$results->totalPages()}";
echo " ({$results->totalPosts()} total events)";

foreach ($results as $event) {
    echo $event->title . "\n";
}

if ($results->hasNextPage()) {
    echo "Next page: " . ($page + 1);
}
```

---

## API Reference

- [`QueryBuilder`](api-reference/query-builder.md)
- [`QueryResult`](api-reference/query-result.md)
- [`QueryArgsDTO`](api-reference/query-args-dto.md)
