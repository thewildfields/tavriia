---
title: Exceptions
description: API reference for all Tavriia exception classes
namespace: TheWildFields\Tavriia\Exceptions
sidebar_position: 30
---

# Exceptions

All Tavriia exceptions extend `\RuntimeException` and live in `TheWildFields\Tavriia\Exceptions\`.

---

## PostNotFoundException

```
TheWildFields\Tavriia\Exceptions\PostNotFoundException
```

```php
final class PostNotFoundException extends \RuntimeException
```

Thrown when a post operation fails or a post cannot be found.

### Static Factory

#### `forId(int $id): self`

```php
public static function forId(int $id): self
```

Creates an exception for a missing post ID.

```php
throw PostNotFoundException::forId(42);
// Message: "Post not found: ID 42"
```

### When it is thrown

| Method | Condition |
|--------|-----------|
| `PostFactory::create()` | WordPress returns WP_Error or 0 |
| `PostFactory::update()` | Post not found or WP_Error |
| `PostFactory::delete()` | Post not found or deletion fails |
| `PostRepository::findById()` | `get_post()` returns null or non-WP_Post |
| `PostRepository::findFirst()` | Query returns no results |

---

## TermNotFoundException

```
TheWildFields\Tavriia\Exceptions\TermNotFoundException
```

```php
final class TermNotFoundException extends \RuntimeException
```

Thrown when a term operation fails or a term cannot be found.

### Static Factories

#### `forId(int $id, string $taxonomy): self`

```php
public static function forId(int $id, string $taxonomy): self
```

```php
throw TermNotFoundException::forId(12, 'event_genre');
// Message: "Term not found: ID 12 in taxonomy 'event_genre'"
```

#### `forField(string $field, string|int $value, string $taxonomy): self`

```php
public static function forField(string $field, string|int $value, string $taxonomy): self
```

```php
throw TermNotFoundException::forField('slug', 'jazz-festival', 'event_genre');
// Message: "Term not found: slug 'jazz-festival' in taxonomy 'event_genre'"
```

### When it is thrown

| Method | Condition |
|--------|-----------|
| `TermFactory::create()` | WordPress returns WP_Error |
| `TermFactory::update()` | Term not found or WP_Error |
| `TermFactory::delete()` | Term not found |
| `TermFactory::setObjectTerms()` | WordPress returns WP_Error |
| `TermFactory::removeObjectTerms()` | WordPress returns WP_Error |
| `TermRepository::findById()` | `get_term()` returns null or WP_Error |
| `TermRepository::findBy()` | `get_term_by()` returns false |
| `TermRepository::findByObject()` | `wp_get_object_terms()` returns WP_Error |

---

## ApiRequestException

```
TheWildFields\Tavriia\Exceptions\ApiRequestException
```

```php
final class ApiRequestException extends \RuntimeException
```

Thrown when an HTTP request cannot be sent due to a transport-level failure (DNS, timeout, SSL, connection refused). The request never reached the server.

### Static Factories

#### `fromWpError(\WP_Error $error): self`

```php
public static function fromWpError(\WP_Error $error): self
```

Creates an exception from a WordPress `WP_Error`, incorporating the WP error message.

#### `forUrl(string $url, string $reason): self`

```php
public static function forUrl(string $url, string $reason): self
```

Creates an exception with a custom reason message.

```php
throw ApiRequestException::forUrl($url, 'Connection timed out');
```

### When it is thrown

| Method | Condition |
|--------|-----------|
| `HttpClient::get()` | `wp_remote_get()` returns WP_Error |
| `HttpClient::post()` | `wp_remote_post()` returns WP_Error |
| `HttpClient::request()` | `wp_remote_request()` returns WP_Error |

---

## ApiResponseException

```
TheWildFields\Tavriia\Exceptions\ApiResponseException
```

```php
final class ApiResponseException extends \RuntimeException
```

Thrown when the server responds with a non-2xx HTTP status code. The request reached the server; the response is attached to the exception.

### Constructor

```php
public function __construct(ApiResponseDTO $response, string $message = '', int $code = 0, ?\Throwable $previous = null)
```

### Static Factory

#### `forResponse(ApiResponseDTO $response): self`

```php
public static function forResponse(ApiResponseDTO $response): self
```

### Instance Method

#### `getResponse(): ApiResponseDTO`

```php
public function getResponse(): ApiResponseDTO
```

Returns the response that caused the exception, allowing access to the status code, body, and headers.

```php
} catch (ApiResponseException $e) {
    $response = $e->getResponse();
    echo $response->statusCode; // e.g. 404
    echo $response->body;
}
```

### When it is thrown

| Method | Condition |
|--------|-----------|
| `HttpClient::get()` | Response status code is not 2xx |
| `HttpClient::post()` | Response status code is not 2xx |
| `HttpClient::request()` | Response status code is not 2xx |

---

## RestRouteRegistrationException

```
TheWildFields\Tavriia\Exceptions\RestRouteRegistrationException
```

```php
final class RestRouteRegistrationException extends \RuntimeException
```

Thrown when registering a REST route with WordPress fails. `register_rest_route()` returns `false` for invalid namespaces or route patterns; the framework converts that silent failure into this typed exception so plugin code never has to inspect boolean return values.

### Static Factories

#### `forRoute(string $namespace, string $route): self`

```php
public static function forRoute(string $namespace, string $route): self
```

```php
throw RestRouteRegistrationException::forRoute('my-plugin/v1', '/events');
// Message: "Failed to register REST route \"/events\" under namespace \"my-plugin/v1\"."
```

#### `forMissingNamespace(): self`

```php
public static function forMissingNamespace(): self
```

Thrown when a `RestRouteDto` or `RestRouteBuilder` is built with an empty namespace.

#### `forMissingRoute(): self`

```php
public static function forMissingRoute(): self
```

Thrown when a `RestRouteDto` or `RestRouteBuilder` is built with an empty route pattern.

### When it is thrown

| Method | Condition |
|--------|-----------|
| `RestRouteBuilder::build()` | Namespace, route, callback, or permission callback is missing |
| `RestServer::register()` | Namespace or route is empty |
| `RestServer::register()` | `register_rest_route()` returns `false` |
| `RestServer::registerMany()` | Any underlying `register()` call fails |

---

## Exception Handling Pattern

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

try {
    $response = $client->get($url);
    $data     = $response->json();
    $postId   = $factory->create($dto);
} catch (ApiRequestException $e) {
    // Transport failure — could not reach the server
} catch (ApiResponseException $e) {
    // Server returned 4xx or 5xx
    $failedResponse = $e->getResponse();
} catch (\JsonException $e) {
    // Response body was not valid JSON
} catch (PostNotFoundException $e) {
    // Post insert failed
} catch (TermNotFoundException $e) {
    // Term not found
}
```

---

## See Also

- [Exception Handling guide](../exceptions.md)
