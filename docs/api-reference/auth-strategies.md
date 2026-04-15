---
title: Auth Strategies
description: API reference for authentication strategies
namespace: TheWildFields\Tavriia\Http\Auth
type: interface-and-implementations
sidebar_position: 14
---

# Auth Strategies

```
TheWildFields\Tavriia\Http\Auth
```

Authentication strategies apply credentials to HTTP requests. They can modify request headers and/or URL query parameters.

---

## AuthStrategyInterface

```php
interface AuthStrategyInterface
```

Contract for authentication strategies.

### Methods

#### `applyTo(RequestBuilder $builder): RequestBuilder`

Apply authentication to the request headers.

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$builder` | `RequestBuilder` | The request builder to modify |

**Returns:** `RequestBuilder` — Modified request builder

---

#### `applyToUrl(string $url): string`

Apply authentication to the URL (for query parameter auth).

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$url` | `string` | The URL to modify |

**Returns:** `string` — Modified URL

---

## BearerTokenAuth

```php
final readonly class BearerTokenAuth implements AuthStrategyInterface
```

Bearer token authentication. Adds an `Authorization: Bearer <token>` header.

### Constructor

```php
public function __construct(string $token)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$token` | `string` | The bearer token |

### Example

```php
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;

$auth = new BearerTokenAuth('sk_live_abc123');

// Applied header: Authorization: Bearer sk_live_abc123
```

---

## ApiKeyAuth

```php
final readonly class ApiKeyAuth implements AuthStrategyInterface
```

API key authentication using a custom header.

### Constructor

```php
public function __construct(
    string $key,
    string $headerName = 'X-API-Key'
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$key` | `string` | — | The API key |
| `$headerName` | `string` | `'X-API-Key'` | Header name to use |

### Example

```php
use TheWildFields\Tavriia\Http\Auth\ApiKeyAuth;

// Default header name
$auth = new ApiKeyAuth('my-api-key');
// Applied header: X-API-Key: my-api-key

// Custom header name
$auth = new ApiKeyAuth('my-api-key', 'X-Custom-Auth');
// Applied header: X-Custom-Auth: my-api-key
```

---

## BasicAuth

```php
final readonly class BasicAuth implements AuthStrategyInterface
```

HTTP Basic authentication. Base64 encodes credentials and adds an `Authorization: Basic <encoded>` header.

### Constructor

```php
public function __construct(
    string $username,
    string $password
)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$username` | `string` | Username |
| `$password` | `string` | Password |

### Example

```php
use TheWildFields\Tavriia\Http\Auth\BasicAuth;

$auth = new BasicAuth('user', 'secret');

// Applied header: Authorization: Basic dXNlcjpzZWNyZXQ=
```

---

## QueryParamAuth

```php
final readonly class QueryParamAuth implements AuthStrategyInterface
```

Query parameter authentication. Appends a key-value pair to the URL query string.

### Constructor

```php
public function __construct(
    string $value,
    string $paramName = 'key'
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$value` | `string` | — | The authentication value |
| `$paramName` | `string` | `'key'` | Query parameter name |

### Example

```php
use TheWildFields\Tavriia\Http\Auth\QueryParamAuth;

// Default parameter name
$auth = new QueryParamAuth('my-api-key');
// URL: https://api.example.com/endpoint?key=my-api-key

// Custom parameter name
$auth = new QueryParamAuth('my-api-key', 'api_key');
// URL: https://api.example.com/endpoint?api_key=my-api-key
```

---

## NoAuth

```php
final readonly class NoAuth implements AuthStrategyInterface
```

No-op authentication for public APIs. Does not modify the request or URL.

### Example

```php
use TheWildFields\Tavriia\Http\Auth\NoAuth;

$auth = new NoAuth();

// No modifications applied
```

---

## See Also

- [API Provider guide](../api-provider.md)
- [`AbstractApiProvider`](abstract-api-provider.md)
- [`ApiProviderConfigDto`](api-provider-config-dto.md)
