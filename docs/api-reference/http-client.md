---
title: HttpClient
description: API reference for TheWildFields\Tavriia\Http\HttpClient
class: HttpClient
namespace: TheWildFields\Tavriia\Http
type: final-class
implements: ApiClientInterface
sidebar_position: 10
---

# HttpClient

```
TheWildFields\Tavriia\Http\HttpClient
```

Executes HTTP requests using WordPress's HTTP API. Wraps `wp_remote_get()`, `wp_remote_post()`, and `wp_remote_request()`. Implements `ApiClientInterface`.

---

## Class Signature

```php
final class HttpClient implements ApiClientInterface
```

### Constructor

```php
public function __construct(ResponseProcessor $processor)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$processor` | `ResponseProcessor` | Converts raw WP responses to `ApiResponseDTO` |

**Example:**
```php
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;

$client = new HttpClient(new ResponseProcessor());
```

---

## Methods

### `get(string $url, array $args = []): ApiResponseDTO`

Sends an HTTP GET request.

```php
public function get(string $url, array $args = []): ApiResponseDTO
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$url` | `string` | — | Request URL |
| `$args` | `array` | `[]` | Additional WP HTTP API arguments |

**Returns:** `ApiResponseDTO`

**Throws:**
- `ApiRequestException` — if WordPress returns a `WP_Error` (transport failure)
- `ApiResponseException` — if the response has a non-2xx status code

**Example:**
```php
$response = $client->get('https://api.example.com/events');
$data = $response->json();
```

---

### `post(string $url, array $args = []): ApiResponseDTO`

Sends an HTTP POST request.

```php
public function post(string $url, array $args = []): ApiResponseDTO
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$url` | `string` | — | Request URL |
| `$args` | `array` | `[]` | WP HTTP API arguments (include `body`, `headers`, etc.) |

**Returns:** `ApiResponseDTO`

**Throws:** `ApiRequestException`, `ApiResponseException`

**Example:**
```php
$response = $client->post('https://api.example.com/events', [
    'body'    => json_encode(['name' => 'Jazz Festival']),
    'headers' => ['Content-Type' => 'application/json'],
]);
```

---

### `request(ApiRequestDTO $request): ApiResponseDTO`

Sends a fully specified HTTP request built from an `ApiRequestDTO`.

```php
public function request(ApiRequestDTO $request): ApiResponseDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$request` | `ApiRequestDTO` | Request DTO built via `RequestBuilder` |

**Returns:** `ApiResponseDTO`

**Throws:** `ApiRequestException`, `ApiResponseException`

**Example:**
```php
use TheWildFields\Tavriia\Http\RequestBuilder;

$request = (new RequestBuilder('https://api.example.com/events'))
    ->method('PATCH')
    ->header('Authorization', 'Bearer ' . $token)
    ->jsonBody(['status' => 'cancelled'])
    ->build();

$response = $client->request($request);
```

---

## See Also

- [HTTP Client guide](../http-client.md)
- [`RequestBuilder`](request-builder.md)
- [`ResponseProcessor`](response-processor.md)
- [`ApiClientInterface`](contracts.md#apiclientinterface)
- [`ApiRequestDTO`](api-request-dto.md)
- [`ApiResponseDTO`](api-response-dto.md)
- [`ApiRequestException`](exceptions.md#apirequestexception)
- [`ApiResponseException`](exceptions.md#apiresponseexception)
