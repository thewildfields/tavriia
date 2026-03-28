# Tavriia — WordPress Framework

## What this is

`thewildfields/tavriia` is a reusable PHP framework that provides a clean, typed, PSR-compliant OOP abstraction layer over WordPress core functions. It is **not a plugin**. It ships as a Composer package consumed by WordPress plugins.

**The framework contains:**
- `AbstractModule` — the base class all plugin modules extend
- Typed wrappers around WordPress core API groups
- Contracts (interfaces) for every wrapper
- DTOs as input/output types across all wrappers

**The framework does NOT contain:**
- Any domain logic
- Any plugin-specific modules (e.g. `EventsModule`, `VenuesModule`)
- Any calls to `register_post_type()` or `register_taxonomy()` directly — those belong in the consuming plugin's modules

---

## Package

```
thewildfields/tavriia
```

Namespace root: `TheWildFields\Tavriia`

PSR-4 autoload maps `TheWildFields\Tavriia\` → `src/`

---

## Requirements

- PHP 8.2+
- WordPress (no minimum enforced by the framework itself)
- PSR-12 coding standard throughout
- No external dependencies beyond WordPress core

---

## Directory structure

```
src/
  AbstractModule.php
  Contracts/
    HasHooksInterface.php
    ApiClientInterface.php
    PostRepositoryInterface.php
    TaxonomyRepositoryInterface.php
    QueryBuilderInterface.php
  Http/
    HttpClient.php          // wraps wp_remote_get / wp_remote_post / wp_remote_request
    RequestBuilder.php      // fluent builder producing ApiRequestDTO
    ResponseProcessor.php   // converts raw WP response → ApiResponseDTO, throws on WP_Error
  Post/
    PostFactory.php         // wraps wp_insert_post / wp_update_post
    PostRepository.php      // wraps get_post / get_posts / WP_Query
    MetaManager.php         // wraps get/update/delete_post_meta
  Taxonomy/
    TermFactory.php         // wraps wp_insert_term / wp_update_term / wp_set_object_terms
    TermRepository.php      // wraps get_terms / get_term_by / wp_get_object_terms
    TermMetaManager.php     // wraps get/update/delete_term_meta
  Query/
    QueryBuilder.php        // fluent builder → WP_Query args, returns QueryResult
    QueryResult.php         // typed wrapper around WP_Query results
  Admin/
    AdminMenuPage.php       // wraps add_menu_page / add_submenu_page
    AdminNotice.php         // wraps admin_notices hook
  DTO/
    PostDTO.php
    TaxonomyDTO.php
    ApiRequestDTO.php
    ApiResponseDTO.php
    QueryArgsDTO.php
  Exceptions/
    PostNotFoundException.php
    TermNotFoundException.php
    ApiRequestException.php
    ApiResponseException.php
```

---

## Design principles

### 1. WP_Error never leaves the framework boundary
Every wrapper converts `WP_Error` into a typed exception at the point of the WP function call. Plugin code never calls `is_wp_error()` on anything this framework returns.

```php
// Good — framework throws, plugin catches
try {
    $id = $this->postFactory->create($dto);
} catch (PostNotFoundException $e) {
    // handle
}

// Bad — never leak WP_Error upward
$result = wp_insert_post($args);
if (is_wp_error($result)) { ... }
```

### 2. Factories return IDs, repositories return DTOs
- `PostFactory::create(PostDTO $dto): int` — returns post ID or throws
- `PostRepository::findById(int $id): PostDTO` — returns typed DTO or throws `PostNotFoundException`
- `PostRepository::findMany(QueryArgsDTO $args): PostDTO[]` — always returns a typed array

### 3. All classes are final unless explicitly designed for extension
Only `AbstractModule` and abstract base classes are non-final. Every concrete wrapper class is `final`.

### 4. Readonly DTOs
All DTOs use `readonly` properties and named constructors where appropriate:

```php
final readonly class PostDTO {
    public function __construct(
        public string $title,
        public string $content,
        public string $status,
        public string $postType,
        public array  $meta = [],
    ) {}

    public static function fromWpPost(\WP_Post $post): self { ... }
}
```

### 5. MetaManager uses typed getters
`MetaManager` and `TermMetaManager` extend a shared `AbstractMetaManager` and expose typed accessors — never raw `get_post_meta()` return values:

```php
$manager->getString('field_key');
$manager->getInt('field_key');
$manager->getBool('field_key');
$manager->getArray('field_key');
```

### 6. QueryBuilder is fluent
`QueryBuilder` produces a `QueryArgsDTO` internally and returns a `QueryResult`. Never build raw `WP_Query` args arrays in plugin code.

```php
$results = $queryBuilder
    ->postType('event')
    ->metaQuery('google_place_id', $placeId)
    ->orderBy('date', 'DESC')
    ->limit(10)
    ->get(); // returns QueryResult
```

### 7. AbstractModule enforces a boot contract
Plugin modules extend `AbstractModule` and implement `register_hooks()`. The framework handles the boot lifecycle — dependency resolution, initialization order.

```php
// In the consuming plugin:
final class EventsModule extends AbstractModule {
    public function __construct(
        private readonly PostFactory $postFactory,
        private readonly PostRepository $postRepository,
    ) {}

    public function register_hooks(): void {
        add_action('init', [$this, 'register_post_type']);
    }
}
```

---

## Exceptions

All exceptions live in `TheWildFields\Tavriia\Exceptions\` and extend `\RuntimeException`. Every public method that can fail throws a typed exception — no nullable returns, no silent failures.

---

## What belongs in the consuming plugin, not here

- `register_post_type()` / `register_taxonomy()` calls
- Any module class (`EventsModule`, `VenuesModule`, etc.)
- Domain-specific DTOs beyond the base set
- Plugin bootstrap / dependency injection container wiring
- Any business logic whatsoever

---

## Build scope (initial)

- [x] `AbstractModule`
- [x] `Contracts`
- [x] `Http` (HttpClient, RequestBuilder, ResponseProcessor)
- [x] `Post` (PostFactory, PostRepository, MetaManager)
- [x] `Taxonomy` (TermFactory, TermRepository, TermMetaManager)
- [x] `Query` (QueryBuilder, QueryResult)
- [x] `Admin` (AdminMenuPage, AdminNotice)
- [x] `DTO` (PostDTO, TaxonomyDTO, ApiRequestDTO, ApiResponseDTO, QueryArgsDTO)
- [x] `Exceptions`