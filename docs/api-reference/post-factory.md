---
title: PostFactory
description: API reference for TheWildFields\Tavriia\Post\PostFactory
class: PostFactory
namespace: TheWildFields\Tavriia\Post
type: final-class
sidebar_position: 2
---

# PostFactory

```
TheWildFields\Tavriia\Post\PostFactory
```

Creates, updates, and deletes WordPress posts. Wraps `wp_insert_post()`, `wp_update_post()`, and `wp_delete_post()`.

---

## Class Signature

```php
final class PostFactory
```

No constructor dependencies.

---

## Methods

### `create(PostDTO $dto): int`

Creates a new WordPress post and writes post meta.

```php
public function create(PostDTO $dto): int
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$dto` | `PostDTO` | Post data to insert |

**Returns:** `int` — the ID of the newly created post.

**Throws:** `PostNotFoundException` if WordPress rejects the insert.

**Behavior:**
- Converts `$dto` to `wp_insert_post()` args
- Calls `wp_insert_post($args, true)` (return `WP_Error` on failure)
- If `$dto->meta` is non-empty, writes each entry via `update_post_meta()`
- Throws on `WP_Error` or non-positive return value

**Example:**
```php
use TheWildFields\Tavriia\DTO\PostDTO;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

$dto = new PostDTO(
    title:    'Summer Concert',
    content:  'Annual outdoor event.',
    status:   'publish',
    postType: 'event',
    meta:     ['venue_id' => 42, 'start_date' => '2026-07-15'],
);

try {
    $postId = $factory->create($dto);
} catch (PostNotFoundException $e) {
    // handle failure
}
```

---

### `update(int $id, PostDTO $dto): int`

Updates an existing WordPress post.

```php
public function update(int $id, PostDTO $dto): int
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$id` | `int` | ID of the post to update |
| `$dto` | `PostDTO` | New post data |

**Returns:** `int` — the post ID (same as `$id` on success).

**Throws:** `PostNotFoundException` if the post does not exist or the update fails.

**Behavior:**
- Merges `$id` with DTO args and calls `wp_update_post($args, true)`
- If `$dto->meta` is non-empty, writes each entry via `update_post_meta()`

**Example:**
```php
$updatedDto = new PostDTO(
    title:    'Summer Concert — Updated',
    content:  $originalContent,
    status:   'publish',
    postType: 'event',
);

$postId = $factory->update($existingId, $updatedDto);
```

---

### `delete(int $id, bool $forceDelete = false): void`

Deletes a WordPress post.

```php
public function delete(int $id, bool $forceDelete = false): void
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$id` | `int` | — | ID of the post to delete |
| `$forceDelete` | `bool` | `false` | If `true`, bypasses trash and permanently deletes |

**Throws:** `PostNotFoundException` if the post does not exist or deletion fails.

**Example:**
```php
$factory->delete($postId);           // moves to trash
$factory->delete($postId, true);     // permanent delete
```

---

## See Also

- [Posts & Meta guide](../posts.md)
- [`PostRepository`](post-repository.md)
- [`PostDTO`](post-dto.md)
- [`PostNotFoundException`](exceptions.md#postnotfoundexception)
