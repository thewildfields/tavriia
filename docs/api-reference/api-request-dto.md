---
title: ApiRequestDTO
description: API reference for TheWildFields\Tavriia\DTO\ApiRequestDTO
class: ApiRequestDTO
namespace: TheWildFields\Tavriia\DTO
type: final-readonly-class
sidebar_position: 22
---

# ApiRequestDTO

```
TheWildFields\Tavriia\DTO\ApiRequestDTO
```

Immutable data transfer object representing an HTTP request. Built via `RequestBuilder` and consumed by `HttpClient::request()`.

---

## Class Signature

```php
final readonly class ApiRequestDTO
```

---

## Constructor

```php
public function __construct(
    public string       $url,
    public string       $method    = 'GET',
    public array        $headers   = [],
    public array|string $body      = '',
    public int          $timeout   = 15,
    public bool         $sslVerify = true,
    public array        $extraArgs = [],
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$url` | `string` | — | Request URL |
| `$method` | `string` | `'GET'` | HTTP method |
| `$headers` | `array` | `[]` | Request headers as `name => value` map |
| `$body` | `array\|string` | `''` | Request body. String for raw/JSON, array for form-encoded |
| `$timeout` | `int` | `15` | Request timeout in seconds |
| `$sslVerify` | `bool` | `true` | Whether to verify SSL certificates |
| `$extraArgs` | `array` | `[]` | Additional WP HTTP API arguments |

---

## Methods

### `toWpArgs(): array`

Converts the DTO to a WordPress-compatible arguments array for `wp_remote_request()`.

```php
public function toWpArgs(): array
```

**Returns:** `array` — merged result of all DTO fields in WP HTTP API format.

The result is passed directly to `wp_remote_request($this->url, $this->toWpArgs())`.

---

## Usage

Typically not constructed directly — use `RequestBuilder`:

```php
use TheWildFields\Tavriia\Http\RequestBuilder;

$request = (new RequestBuilder('https://api.example.com/events'))
    ->method('POST')
    ->header('Authorization', 'Bearer ' . $token)
    ->jsonBody(['name' => 'Jazz Festival'])
    ->timeout(30)
    ->build(); // returns ApiRequestDTO
```

If you need to construct manually:

```php
use TheWildFields\Tavriia\DTO\ApiRequestDTO;

$request = new ApiRequestDTO(
    url:     'https://api.example.com/events',
    method:  'POST',
    headers: ['Authorization' => 'Bearer ' . $token],
    body:    json_encode(['name' => 'Jazz Festival']),
    timeout: 30,
);
```

---

## See Also

- [HTTP Client guide](../http-client.md)
- [`RequestBuilder`](request-builder.md)
- [`HttpClient`](http-client.md)
- [`ApiResponseDTO`](api-response-dto.md)
