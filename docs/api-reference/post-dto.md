---
title: PostDTO
description: API reference for TheWildFields\Tavriia\DTO\PostDTO
class: PostDTO
namespace: TheWildFields\Tavriia\DTO
type: final-readonly-class
sidebar_position: 20
---

# PostDTO

```
TheWildFields\Tavriia\DTO\PostDTO
```

Immutable data transfer object representing a WordPress post. Used as input to `PostFactory` methods and returned by `PostRepository` methods.

---

## Class Signature

```php
final readonly class PostDTO
```

---

## Constructor

```php
public function __construct(
    public string  $title,
    public string  $content,
    public string  $status,
    public string  $postType,
    public string  $excerpt        = '',
    public string  $slug           = '',
    public string  $password       = '',
    public string  $pingStatus     = '',
    public string  $commentStatus  = '',
    public string  $mimeType       = '',
    public int     $authorId       = 0,
    public int     $parentId       = 0,
    public int     $menuOrder      = 0,
    public int     $id             = 0,
    public ?string $dateGmt        = null,
    public array   $meta           = [],
    public array   $termIds        = [],
)
```

**Required parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$title` | `string` | Post title |
| `$content` | `string` | Post content (HTML) |
| `$status` | `string` | Post status: `'publish'`, `'draft'`, `'pending'`, `'private'`, etc. |
| `$postType` | `string` | Registered post type slug |

**Optional parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$excerpt` | `string` | `''` | Post excerpt |
| `$slug` | `string` | `''` | URL slug (post name). WordPress generates one from title if empty |
| `$password` | `string` | `''` | Post password for protected posts |
| `$pingStatus` | `string` | `''` | Ping status: `'open'` or `'closed'` |
| `$commentStatus` | `string` | `''` | Comment status: `'open'` or `'closed'` |
| `$mimeType` | `string` | `''` | MIME type (for attachments) |
| `$authorId` | `int` | `0` | Post author user ID. `0` = omit from args (uses current user) |
| `$parentId` | `int` | `0` | Parent post ID. `0` = no parent |
| `$menuOrder` | `int` | `0` | Menu/sort order |
| `$id` | `int` | `0` | Post ID. `0` = new post (not yet saved) |
| `$dateGmt` | `?string` | `null` | Publication date in GMT (format: `Y-m-d H:i:s`). `null` = current time |
| `$meta` | `array` | `[]` | Key → value pairs to write via `update_post_meta()` after save |
| `$termIds` | `array` | `[]` | Reserved for future use |

---

## Static Factory Methods

### `fromWpPost(\WP_Post $post): self`

Constructs a `PostDTO` from a WordPress `WP_Post` object.

```php
public static function fromWpPost(\WP_Post $post): self
```

Used internally by `PostRepository`. Useful when you have a `WP_Post` and need a typed DTO.

```php
$wpPost = get_post(42);
if ($wpPost instanceof \WP_Post) {
    $dto = PostDTO::fromWpPost($wpPost);
}
```

---

### `withId(int $id): self`

Returns a new `PostDTO` instance identical to the current one but with the given `$id`.

```php
public function withId(int $id): self
```

Used internally after `PostFactory::create()` to attach the new ID:

```php
$postId = $factory->create($dto);
$dtoWithId = $dto->withId($postId);
```

---

## Examples

### Minimal post

```php
$dto = new PostDTO(
    title:    'Untitled Event',
    content:  '',
    status:   'draft',
    postType: 'event',
);
```

### Full post with meta

```php
$dto = new PostDTO(
    title:         'Summer Jazz Festival',
    content:       '<p>Annual outdoor jazz festival.</p>',
    status:        'publish',
    postType:      'event',
    excerpt:       'Annual outdoor jazz festival in the park.',
    slug:          'summer-jazz-festival-2026',
    authorId:      3,
    meta: [
        'venue_id'    => 42,
        'start_date'  => '2026-07-15',
        'ticket_url'  => 'https://tickets.example.com/jazz',
        'is_featured' => true,
    ],
);
```

---

## See Also

- [Posts & Meta guide](../posts.md)
- [`PostFactory`](post-factory.md)
- [`PostRepository`](post-repository.md)
