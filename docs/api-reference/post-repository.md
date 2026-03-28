---
title: PostRepository
description: API reference for TheWildFields\Tavriia\Post\PostRepository
class: PostRepository
namespace: TheWildFields\Tavriia\Post
type: final-class
implements: PostRepositoryInterface
sidebar_position: 3
---

# PostRepository

```
TheWildFields\Tavriia\Post\PostRepository
```

Reads WordPress posts and returns typed DTOs. Wraps `get_post()` and `get_posts()`. Implements `PostRepositoryInterface`.

---

## Class Signature

```php
final class PostRepository implements PostRepositoryInterface
```

No constructor dependencies.

---

## Methods

### `findById(int $id): PostDTO`

Fetches a single post by ID.

```php
public function findById(int $id): PostDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | WordPress post ID |

**Returns:** `PostDTO`

**Throws:** `PostNotFoundException` if no post exists with the given ID.

**Example:**
```php
try {
    $post = $repository->findById(42);
    echo $post->title;
} catch (PostNotFoundException $e) {
    // post does not exist
}
```

---

### `findMany(QueryArgsDTO $args): PostDTO[]`

Fetches multiple posts matching the given query arguments.

```php
public function findMany(QueryArgsDTO $args): PostDTO[]
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$args` | `QueryArgsDTO` | Query arguments |

**Returns:** `PostDTO[]` — always an array, never `false` or `null`. Returns `[]` when no posts match.

**Never throws.**

**Example:**
```php
use TheWildFields\Tavriia\DTO\QueryArgsDTO;

$args = new QueryArgsDTO(
    postType:     'event',
    postStatus:   'publish',
    postsPerPage: 20,
    orderBy:      'date',
    order:        'DESC',
);

$posts = $repository->findMany($args);

foreach ($posts as $post) {
    echo $post->title . "\n";
}
```

---

### `findFirst(QueryArgsDTO $args): PostDTO`

Fetches the first post matching the given query arguments.

```php
public function findFirst(QueryArgsDTO $args): PostDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$args` | `QueryArgsDTO` | Query arguments (limit forced to 1 internally) |

**Returns:** `PostDTO`

**Throws:** `PostNotFoundException` if no posts match the query.

**Example:**
```php
try {
    $latest = $repository->findFirst(new QueryArgsDTO(
        postType:   'event',
        postStatus: 'publish',
        orderBy:    'date',
        order:      'DESC',
    ));
} catch (PostNotFoundException $e) {
    // no events found
}
```

---

### `exists(int $id): bool`

Checks whether a post with the given ID exists.

```php
public function exists(int $id): bool
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | WordPress post ID |

**Returns:** `bool` — `true` if the post exists, `false` otherwise.

**Never throws.**

**Example:**
```php
if ($repository->exists($postId)) {
    $factory->update($postId, $dto);
} else {
    $factory->create($dto);
}
```

---

### `metaFor(int $postId): MetaManager`

Returns a `MetaManager` instance for the given post ID without fetching the post.

```php
public function metaFor(int $postId): MetaManager
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$postId` | `int` | WordPress post ID |

**Returns:** `MetaManager`

**Never throws.** Does not verify the post exists.

**Example:**
```php
$meta = $repository->metaFor($postId);
$venueName = $meta->getString('venue_name');
$capacity  = $meta->getInt('capacity', 50);
```

---

## See Also

- [Posts & Meta guide](../posts.md)
- [`PostFactory`](post-factory.md)
- [`MetaManager`](meta-manager.md)
- [`PostDTO`](post-dto.md)
- [`QueryArgsDTO`](query-args-dto.md)
- [`PostRepositoryInterface`](contracts.md#postrepositoryinterface)
- [`PostNotFoundException`](exceptions.md#postnotfoundexception)
