---
title: RequestBuilder
description: API reference for TheWildFields\Tavriia\Http\RequestBuilder
class: RequestBuilder
namespace: TheWildFields\Tavriia\Http
type: final-class
sidebar_position: 11
---

# RequestBuilder

```
TheWildFields\Tavriia\Http\RequestBuilder
```

Fluent, immutable builder for `ApiRequestDTO`. Every setter returns a cloned instance — the original is never mutated.

---

## Class Signature

```php
final class RequestBuilder
```

### Constructor

```php
public function __construct(string $url)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$url` | `string` | Target URL for the request |

---

## Methods

### `method(string $method): self`

Sets the HTTP method.

```php
public function method(string $method): self
```

Default: `'GET'`

```php
$builder->method('POST');
$builder->method('PUT');
$builder->method('PATCH');
$builder->method('DELETE');
```

---

### `header(string $name, string $value): self`

Adds a single request header.

```php
public function header(string $name, string $value): self
```

```php
$builder->header('Authorization', 'Bearer ' . $token);
$builder->header('Accept', 'application/json');
```

---

### `headers(array $headers): self`

Adds multiple request headers at once.

```php
public function headers(array $headers): self
```

```php
$builder->headers([
    'Authorization' => 'Bearer ' . $token,
    'Accept'        => 'application/json',
    'X-Version'     => '2',
]);
```

---

### `body(array|string $body): self`

Sets the request body as a raw string or array.

```php
public function body(array|string $body): self
```

```php
$builder->body('raw string body');
$builder->body(['key' => 'value']); // WordPress will form-encode this
```

---

### `jsonBody(array $data): self`

Sets the request body as a JSON-encoded string and adds the `Content-Type: application/json` header.

```php
public function jsonBody(array $data): self
```

```php
$builder->jsonBody([
    'name'  => 'Jazz Festival',
    'date'  => '2026-07-15',
    'slots' => 250,
]);
```

---

### `timeout(int $seconds): self`

Sets the request timeout in seconds.

```php
public function timeout(int $seconds): self
```

Default: `15`

```php
$builder->timeout(30);
$builder->timeout(60);
```

---

### `sslVerify(bool $verify): self`

Controls whether SSL certificates are verified.

```php
public function sslVerify(bool $verify): self
```

Default: `true`. Only disable for development/testing environments.

```php
$builder->sslVerify(false); // development only
```

---

### `withArgs(array $args): self`

Merges additional WordPress HTTP API arguments not covered by the fluent methods.

```php
public function withArgs(array $args): self
```

```php
$builder->withArgs([
    'redirection' => 0,
    'cookies'     => [...],
    'user-agent'  => 'MyPlugin/1.0',
]);
```

---

### `build(): ApiRequestDTO`

Builds and returns the `ApiRequestDTO`.

```php
public function build(): ApiRequestDTO
```

**Returns:** `ApiRequestDTO`

---

## Full Example

```php
use TheWildFields\Tavriia\Http\RequestBuilder;

$request = (new RequestBuilder('https://api.example.com/events'))
    ->method('POST')
    ->header('Authorization', 'Bearer ' . $apiKey)
    ->header('X-Source', 'my-plugin')
    ->jsonBody([
        'name'     => 'Jazz Festival',
        'venue_id' => 42,
        'date'     => '2026-07-15',
    ])
    ->timeout(30)
    ->build();

$response = $client->request($request);
```

---

## See Also

- [HTTP Client guide](../http-client.md)
- [`HttpClient`](http-client.md)
- [`ApiRequestDTO`](api-request-dto.md)
