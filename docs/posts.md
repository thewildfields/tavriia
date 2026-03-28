---
title: Posts & Meta
description: Create, update, delete, and query WordPress posts with type safety
sidebar_position: 4
---

# Posts & Meta

Tavriia provides three classes for working with WordPress posts:

| Class | Responsibility |
|-------|---------------|
| `PostFactory` | Create, update, delete posts |
| `PostRepository` | Read posts by ID or query |
| `MetaManager` | Read and write post meta with typed accessors |

---

## PostDTO

All post data is represented as a `PostDTO`. It is a `readonly` value object — construct it once and pass it around.

```php
use TheWildFields\Tavriia\DTO\PostDTO;

$dto = new PostDTO(
    title: 'Summer Concert 2026',
    content: '<p>Annual outdoor concert.</p>',
    status: 'publish',
    postType: 'event',
    excerpt: 'Annual outdoor concert in the park.',
    meta: [
        'venue_id'   => 42,
        'start_date' => '2026-07-15',
        'ticket_url' => 'https://tickets.example.com',
    ],
);
```

All parameters except `title`, `content`, `status`, and `postType` are optional with sensible defaults.

`PostDTO` can also be constructed from a `WP_Post` object:

```php
$dto = PostDTO::fromWpPost($wpPost);
```

---

## PostFactory

### Creating a Post

```php
use TheWildFields\Tavriia\Post\PostFactory;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

$factory = new PostFactory();

try {
    $postId = $factory->create($dto);
} catch (PostNotFoundException $e) {
    // WordPress rejected the insert — check $e->getMessage()
}
```

`create()` inserts the post, then writes all `$dto->meta` entries via `update_post_meta`. Returns the new post ID as `int`.

### Updating a Post

```php
$updatedDto = new PostDTO(
    title: 'Summer Concert 2026 — Rescheduled',
    content: $originalDto->content,
    status: 'publish',
    postType: 'event',
    meta: ['start_date' => '2026-08-01'],
);

try {
    $postId = $factory->update($existingId, $updatedDto);
} catch (PostNotFoundException $e) {
    // Post not found or update failed
}
```

Only fields present in the DTO are sent to WordPress. Meta is written only when `$dto->meta` is non-empty.

### Deleting a Post

```php
try {
    $factory->delete($postId);              // moves to trash
    $factory->delete($postId, true);        // permanently deletes
} catch (PostNotFoundException $e) {
    // Post does not exist
}
```

---

## PostRepository

### Find by ID

```php
use TheWildFields\Tavriia\Post\PostRepository;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

$repository = new PostRepository();

try {
    $post = $repository->findById($postId);
    echo $post->title;
    echo $post->status;
    echo $post->postType;
} catch (PostNotFoundException $e) {
    // No post with this ID
}
```

### Find Many

```php
use TheWildFields\Tavriia\DTO\QueryArgsDTO;

$args = new QueryArgsDTO(
    postType: 'event',
    postStatus: 'publish',
    postsPerPage: 20,
);

$posts = $repository->findMany($args); // PostDTO[]
foreach ($posts as $post) {
    echo $post->title . "\n";
}
```

`findMany()` always returns an array (never `false` or `null`). An empty result set returns `[]`.

### Find First

```php
try {
    $latestEvent = $repository->findFirst($args);
} catch (PostNotFoundException $e) {
    // No posts match the query
}
```

### Check Existence

```php
if ($repository->exists($postId)) {
    // post exists
}
```

### Get Meta Manager

```php
$meta = $repository->metaFor($postId);
// Returns MetaManager without fetching the post itself
```

---

## MetaManager

Typed accessors for post meta. Obtain a `MetaManager` via `PostRepository::metaFor()`.

```php
$meta = $repository->metaFor($postId);

$venueName  = $meta->getString('venue_name');          // string, default ''
$capacity   = $meta->getInt('capacity');               // int, default 0
$isOpen     = $meta->getBool('is_open');               // bool, default false
$tags       = $meta->getArray('tags');                 // array, default []
$price      = $meta->getFloat('ticket_price');         // float, default 0.0
```

All getters accept an optional second argument for a custom default:

```php
$status = $meta->getString('status', 'pending');
$limit  = $meta->getInt('capacity', 100);
```

### Writing Meta

```php
$meta->set('venue_id', 42);
$meta->set('tags', ['music', 'outdoor']);
```

### Deleting Meta

```php
$meta->delete('old_field');
```

### Checking Existence

```php
if ($meta->has('venue_id')) {
    $id = $meta->getInt('venue_id');
}
```

### Reading All Meta

```php
$allMeta = $meta->all(); // array<string, mixed>
```

---

## Boolean Meta Handling

`getBool()` treats any of these values as `true` (case-insensitive): `1`, `'1'`, `'true'`, `'yes'`, `'on'`. Everything else is `false`. This matches common patterns in WordPress ACF and CMB2 field output.

---

## Error Handling Summary

| Operation | Throws |
|-----------|--------|
| `create()` | `PostNotFoundException` if WordPress rejects the insert |
| `update()` | `PostNotFoundException` if post not found or update fails |
| `delete()` | `PostNotFoundException` if post not found or deletion fails |
| `findById()` | `PostNotFoundException` if no post with that ID |
| `findFirst()` | `PostNotFoundException` if query returns no results |

`findMany()` and `exists()` never throw.

---

## API Reference

- [`PostFactory`](api-reference/post-factory.md)
- [`PostRepository`](api-reference/post-repository.md)
- [`MetaManager`](api-reference/meta-manager.md)
- [`PostDTO`](api-reference/post-dto.md)
- [`PostNotFoundException`](api-reference/exceptions.md#postnotfoundexception)
