---
title: ApiProviderConfigDto
description: API reference for TheWildFields\Tavriia\Dto\ApiProviderConfigDto
class: ApiProviderConfigDto
namespace: TheWildFields\Tavriia\Dto
type: final-readonly-class
sidebar_position: 15
---

# ApiProviderConfigDto

```
TheWildFields\Tavriia\Dto\ApiProviderConfigDto
```

Immutable configuration data transfer object for API providers.

---

## Class Signature

```php
final readonly class ApiProviderConfigDto
```

### Constructor

```php
public function __construct(
    string $baseUrl,
    AuthStrategyInterface $auth = new NoAuth(),
    array $defaultHeaders = [],
    int $timeout = 15,
    bool $sslVerify = true,
)
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `$baseUrl` | `string` | — | Base URL for all requests (without trailing slash) |
| `$auth` | `AuthStrategyInterface` | `new NoAuth()` | Authentication strategy |
| `$defaultHeaders` | `array<string, string>` | `[]` | Default headers for all requests |
| `$timeout` | `int` | `15` | Request timeout in seconds |
| `$sslVerify` | `bool` | `true` | Whether to verify SSL certificates |

---

## Properties

All properties are `public readonly`.

### `$baseUrl`

```php
public string $baseUrl
```

Base URL for all API requests. Should not include a trailing slash.

### `$auth`

```php
public AuthStrategyInterface $auth
```

Authentication strategy to apply to all requests.

### `$defaultHeaders`

```php
public array $defaultHeaders
```

Default headers to include with every request. Can be overridden per-request.

### `$timeout`

```php
public int $timeout
```

Request timeout in seconds.

### `$sslVerify`

```php
public bool $sslVerify
```

Whether to verify SSL certificates.

---

## Examples

### Minimal Configuration

```php
use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;

$config = new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com/v1',
);
```

### Full Configuration

```php
use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;

$config = new ApiProviderConfigDto(
    baseUrl: 'https://api.stripe.com/v1',
    auth: new BearerTokenAuth($secretKey),
    defaultHeaders: [
        'Accept' => 'application/json',
        'Stripe-Version' => '2023-10-16',
    ],
    timeout: 30,
    sslVerify: true,
);
```

---

## See Also

- [API Provider guide](../api-provider.md)
- [`AbstractApiProvider`](abstract-api-provider.md)
- [Auth Strategies](auth-strategies.md)
