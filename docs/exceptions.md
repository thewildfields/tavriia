---
title: Exception Handling
description: Understanding Tavriia's exception hierarchy and error handling patterns
sidebar_position: 9
---

# Exception Handling

Tavriia converts all WordPress errors into typed PHP exceptions at the framework boundary. Your plugin code deals only with PHP exceptions — never `WP_Error`, `false`, or `null` return values from WordPress functions.

---

## Exception Hierarchy

All exceptions extend `\RuntimeException` and live in `TheWildFields\Tavriia\Exceptions\`.

```
\RuntimeException
├── PostNotFoundException
├── TermNotFoundException
├── ApiRequestException
└── ApiResponseException
```

---

## PostNotFoundException

Thrown when a post operation fails or a post cannot be found.

**When it is thrown:**
- `PostFactory::create()` — WordPress rejects the insert
- `PostFactory::update()` — post not found or update fails
- `PostFactory::delete()` — post not found or deletion fails
- `PostRepository::findById()` — no post with the given ID
- `PostRepository::findFirst()` — query returns no results

```php
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

try {
    $post = $this->postRepository->findById($id);
} catch (PostNotFoundException $e) {
    // $e->getMessage() contains the post ID and context
    return null;
}
```

---

## TermNotFoundException

Thrown when a term operation fails or a term cannot be found.

**When it is thrown:**
- `TermFactory::create()` — WordPress rejects the insert
- `TermFactory::update()` — term not found or update fails
- `TermFactory::delete()` — term not found
- `TermFactory::setObjectTerms()` — WordPress returns WP_Error
- `TermRepository::findById()` — no term with the given ID
- `TermRepository::findBy()` — no matching term
- `TermRepository::findByObject()` — WordPress returns WP_Error

```php
use TheWildFields\Tavriia\Exceptions\TermNotFoundException;

try {
    $term = $this->termRepository->findBy('slug', 'jazz-festival', 'event_genre');
} catch (TermNotFoundException $e) {
    // Term does not exist
}
```

---

## ApiRequestException

Thrown when an HTTP request cannot be sent — a transport-level failure.

**When it is thrown:**
- `HttpClient::get()`, `::post()`, `::request()` — when WordPress returns a `WP_Error` (DNS failure, timeout, SSL error, connection refused)

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;

try {
    $response = $this->httpClient->get($url);
} catch (ApiRequestException $e) {
    // Network failure — the request never reached the server
    error_log('Transport error: ' . $e->getMessage());
}
```

---

## ApiResponseException

Thrown when the server responds with a non-2xx HTTP status code.

**When it is thrown:**
- Any HTTP method returns a 4xx or 5xx status code

Unlike `ApiRequestException`, the request *did* reach the server and *did* receive a response. The failed response is accessible via `getResponse()`:

```php
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

try {
    $response = $this->httpClient->get($url);
} catch (ApiResponseException $e) {
    $failedResponse = $e->getResponse();

    if ($failedResponse->isClientError()) {
        // 4xx — bad request, auth failure, not found, etc.
        error_log("Client error {$failedResponse->statusCode}: {$failedResponse->body}");
    }

    if ($failedResponse->isServerError()) {
        // 5xx — server-side failure
        error_log("Server error {$failedResponse->statusCode}");
    }
}
```

---

## Handling All Exceptions Together

For operations that can fail multiple ways:

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;
use TheWildFields\Tavriia\Exceptions\PostNotFoundException;

try {
    $response = $this->httpClient->get($apiUrl);
    $payload  = $response->json();

    $dto = new PostDTO(
        title:    $payload['title'],
        content:  $payload['body'],
        status:   'publish',
        postType: 'event',
    );

    $postId = $this->postFactory->create($dto);

} catch (ApiRequestException $e) {
    // Could not reach the API
} catch (ApiResponseException $e) {
    // API returned an error status
} catch (\JsonException $e) {
    // Response body was not valid JSON
} catch (PostNotFoundException $e) {
    // Post insert failed
}
```

---

## Static Factory Methods

Exceptions include static factory methods for constructing descriptive instances:

```php
// PostNotFoundException
PostNotFoundException::forId(42);
// "Post not found: ID 42"

// TermNotFoundException
TermNotFoundException::forId(12, 'event_genre');
// "Term not found: ID 12 in taxonomy 'event_genre'"

TermNotFoundException::forField('slug', 'jazz-festival', 'event_genre');
// "Term not found: slug 'jazz-festival' in taxonomy 'event_genre'"

// ApiRequestException
ApiRequestException::fromWpError($wpError);
ApiRequestException::forUrl($url, 'Connection timed out');

// ApiResponseException
ApiResponseException::forResponse($apiResponseDto);
```

These are used internally by the framework. You can use them in tests or when building custom wrappers.

---

## Operations That Never Throw

Some repository methods are designed to always return safely:

| Method | Returns when nothing found |
|--------|---------------------------|
| `PostRepository::findMany()` | `[]` |
| `PostRepository::exists()` | `false` |
| `TermRepository::findAll()` | `[]` |
| `TermRepository::findByObject()` | `[]` (unless WP_Error) |
| `TermRepository::exists()` | `false` |

---

## API Reference

- [`PostNotFoundException`](api-reference/exceptions.md#postnotfoundexception)
- [`TermNotFoundException`](api-reference/exceptions.md#termnotfoundexception)
- [`ApiRequestException`](api-reference/exceptions.md#apirequestexception)
- [`ApiResponseException`](api-reference/exceptions.md#apiresponseexception)
