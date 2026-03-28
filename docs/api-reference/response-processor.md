---
title: ResponseProcessor
description: API reference for TheWildFields\Tavriia\Http\ResponseProcessor
class: ResponseProcessor
namespace: TheWildFields\Tavriia\Http
type: final-class
sidebar_position: 12
---

# ResponseProcessor

```
TheWildFields\Tavriia\Http\ResponseProcessor
```

Converts raw WordPress HTTP responses into typed `ApiResponseDTO` objects, and throws typed exceptions on errors. This class enforces the framework's boundary — `WP_Error` never escapes.

---

## Class Signature

```php
final class ResponseProcessor
```

No constructor dependencies.

---

## Methods

### `process(\WP_Error|array $rawResponse): ApiResponseDTO`

Converts a raw WordPress HTTP response to `ApiResponseDTO`.

```php
public function process(\WP_Error|array $rawResponse): ApiResponseDTO
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$rawResponse` | `\WP_Error\|array` | Raw return value from `wp_remote_get()` / `wp_remote_post()` / `wp_remote_request()` |

**Returns:** `ApiResponseDTO`

**Throws:**
- `ApiRequestException` — if `$rawResponse` is a `WP_Error` (transport-level failure)
- `ApiResponseException` — if the HTTP status code is not in the 2xx range

**Processing steps:**
1. If `$rawResponse` is `WP_Error` → throw `ApiRequestException::fromWpError($rawResponse)`
2. Extract status code from response using `wp_remote_retrieve_response_code()`
3. Extract body using `wp_remote_retrieve_body()`
4. Normalize headers via internal helper
5. If status code is not 2xx → build `ApiResponseDTO`, then throw `ApiResponseException::forResponse($dto)`
6. Return `ApiResponseDTO` on success

---

## Usage

`ResponseProcessor` is a dependency of `HttpClient`. You typically do not call it directly.

```php
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;

$client = new HttpClient(new ResponseProcessor());
```

If you are building a custom HTTP client that implements `ApiClientInterface`, inject `ResponseProcessor` and call `process()` on the raw WordPress response:

```php
final class CachedHttpClient implements ApiClientInterface
{
    public function __construct(
        private readonly ResponseProcessor $processor,
        private readonly CacheInterface    $cache,
    ) {}

    public function get(string $url, array $args = []): ApiResponseDTO
    {
        $cacheKey = 'http_' . md5($url);
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $raw      = wp_remote_get($url, $args);
        $response = $this->processor->process($raw);

        $this->cache->set($cacheKey, $response, 300);
        return $response;
    }
}
```

---

## See Also

- [HTTP Client guide](../http-client.md)
- [`HttpClient`](http-client.md)
- [`ApiResponseDTO`](api-response-dto.md)
- [`ApiRequestException`](exceptions.md#apirequestexception)
- [`ApiResponseException`](exceptions.md#apiresponseexception)
