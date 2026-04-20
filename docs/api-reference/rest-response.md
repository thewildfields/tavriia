---
title: RestResponse
description: API reference for TheWildFields\Tavriia\Rest\RestResponse
class: RestResponse
namespace: TheWildFields\Tavriia\Rest
type: final-class
sidebar_position: 18
---

# RestResponse

```
TheWildFields\Tavriia\Rest\RestResponse
```

Typed response object for REST API callbacks. `RestResponse` is the framework-native value plugin callbacks return; calling `toWp()` converts it into a `WP_REST_Response` (success) or `WP_Error` (error) at the WordPress boundary.

---

## Class Signature

```php
final class RestResponse
```

Private constructor — use the named factories below.

---

## Success Factories

### `ok(mixed $data = null, array $headers = []): self`

Builds a 200 OK response.

```php
RestResponse::ok(['id' => 1, 'title' => 'Summer Concert']);
RestResponse::ok($events, ['X-Total-Count' => '42']);
```

---

### `created(mixed $data = null, array $headers = []): self`

Builds a 201 Created response. Use after creating a resource.

```php
RestResponse::created(['id' => $newEventId]);
```

---

### `noContent(): self`

Builds a 204 No Content response. No body, no headers.

```php
RestResponse::noContent();
```

---

## Error Factories

### `error(string $code, string $message, int $status = 400, array $data = []): self`

Generic error factory. All other error factories delegate to this.

```php
RestResponse::error('validation_failed', 'Invalid input.', 422, [
    'fields' => ['start_date', 'venue_id'],
]);
```

---

### Error shortcuts

```php
public static function badRequest(string $code, string $message, array $data = []): self   // 400
public static function unauthorized(string $code, string $message, array $data = []): self  // 401
public static function forbidden(string $code, string $message, array $data = []): self     // 403
public static function notFound(string $code, string $message, array $data = []): self      // 404
public static function serverError(string $code, string $message, array $data = []): self   // 500
```

**Example:**
```php
RestResponse::badRequest('invalid_title', 'Title is required.');
RestResponse::unauthorized('no_auth', 'Authentication required.');
RestResponse::forbidden('no_perm', 'You cannot edit this event.');
RestResponse::notFound('event_not_found', "No event with ID {$id}.");
RestResponse::serverError('sync_failed', 'Upstream sync failed.');
```

---

## WordPress Boundary

### `toWp(): WP_REST_Response|WP_Error`

Converts this response to the native WordPress REST type.

```php
public function toWp(): \WP_REST_Response|\WP_Error
```

**Returns:**

- `WP_REST_Response` — for success responses. Data, status, and headers are set.
- `WP_Error` — for error responses. Code, message, and a `data` array containing the HTTP `status` and any extra error context.

**Example:**
```php
public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
{
    try {
        $event = $this->repository->findById((int) $request['id']);
        return RestResponse::ok($event)->toWp();
    } catch (PostNotFoundException $e) {
        return RestResponse::notFound('event_not_found', $e->getMessage())->toWp();
    }
}
```

---

## Accessors

### `data(): mixed`

Returns the response payload (success responses only; `null` for errors).

### `status(): int`

Returns the HTTP status code.

### `headers(): array<string, string>`

Returns any headers set on a success response.

### `isError(): bool`

Returns whether this response represents an error.

### `errorCode(): string`

Returns the error code (empty string for success responses).

### `errorMessage(): string`

Returns the error message (empty string for success responses).

### `errorData(): array<string, mixed>`

Returns any additional error context (empty array for success responses).

---

## See Also

- [REST API guide](../rest.md)
- [`RestServer`](rest-server.md)
- [`RestRouteBuilder`](rest-route-builder.md)
- [`AbstractRestController`](abstract-rest-controller.md)
