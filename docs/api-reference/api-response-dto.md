---
title: ApiResponseDTO
description: API reference for TheWildFields\Tavriia\DTO\ApiResponseDTO
class: ApiResponseDTO
namespace: TheWildFields\Tavriia\DTO
type: final-readonly-class
sidebar_position: 23
---

# ApiResponseDTO

```
TheWildFields\Tavriia\DTO\ApiResponseDTO
```

Immutable data transfer object representing an HTTP response. Returned by all `HttpClient` methods.

---

## Class Signature

```php
final readonly class ApiResponseDTO
```

---

## Constructor

```php
public function __construct(
    public int    $statusCode,
    public string $body,
    public array  $headers,
)
```

**Properties:**

| Name | Type | Description |
|------|------|-------------|
| `$statusCode` | `int` | HTTP status code (e.g. `200`, `404`, `500`) |
| `$body` | `string` | Raw response body |
| `$headers` | `array` | Response headers as `name => value` map |

---

## Methods

### `json(): mixed`

Decodes the response body as JSON.

```php
public function json(): mixed
```

**Returns:** Decoded PHP value (usually `array` or `object`).

**Throws:** `\JsonException` if the body is not valid JSON.

```php
$data = $response->json();
echo $data['name'];
```

---

### `isSuccess(): bool`

Returns `true` if the status code is in the 2xx range.

```php
public function isSuccess(): bool
```

```php
if ($response->isSuccess()) {
    $data = $response->json();
}
```

Note: `HttpClient` always returns a successful `ApiResponseDTO` (2xx). Non-2xx responses throw `ApiResponseException`. `isSuccess()` is primarily useful when using `ResponseProcessor` directly or accessing the response attached to `ApiResponseException`.

---

### `isClientError(): bool`

Returns `true` if the status code is in the 4xx range.

```php
public function isClientError(): bool
```

```php
} catch (ApiResponseException $e) {
    if ($e->getResponse()->isClientError()) {
        // bad request, auth failure, not found
    }
}
```

---

### `isServerError(): bool`

Returns `true` if the status code is in the 5xx range.

```php
public function isServerError(): bool
```

---

## Example

```php
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

try {
    $response = $client->get('https://api.example.com/events');

    // Always 2xx here — HttpClient throws for non-2xx
    $events = $response->json();

} catch (ApiResponseException $e) {
    $failed = $e->getResponse();

    echo "Status: {$failed->statusCode}";
    echo "Body: {$failed->body}";

    if ($failed->isClientError()) {
        // 400-499
    }
    if ($failed->isServerError()) {
        // 500-599
    }
}
```

---

## See Also

- [HTTP Client guide](../http-client.md)
- [`HttpClient`](http-client.md)
- [`ApiRequestDTO`](api-request-dto.md)
- [`ApiResponseException`](exceptions.md#apiresponseexception)
