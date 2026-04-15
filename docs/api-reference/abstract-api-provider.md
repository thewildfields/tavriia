---
title: AbstractApiProvider
description: API reference for TheWildFields\Tavriia\Http\AbstractApiProvider
class: AbstractApiProvider
namespace: TheWildFields\Tavriia\Http
type: abstract-class
sidebar_position: 13
---

# AbstractApiProvider

```
TheWildFields\Tavriia\Http\AbstractApiProvider
```

Abstract base class for API providers. Extend this class to define external API services with a base URL, authentication, and default settings.

---

## Class Signature

```php
abstract class AbstractApiProvider
```

### Constructor

```php
public function __construct(HttpClient $httpClient)
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$httpClient` | `HttpClient` | The HTTP client to use for requests |

---

## Abstract Methods

### `configure(): ApiProviderConfigDto`

Configure the API provider. Must return an `ApiProviderConfigDto` defining the base URL, authentication strategy, and default settings.

```php
abstract protected function configure(): ApiProviderConfigDto
```

**Returns:** `ApiProviderConfigDto`

---

## Protected Methods

### `get(string $endpoint, array $queryParams = []): ApiResponseDto`

Send a GET request to the given endpoint.

```php
protected function get(string $endpoint, array $queryParams = []): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$endpoint` | `string` | API endpoint (relative to base URL) |
| `$queryParams` | `array<string, mixed>` | Query parameters to append |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

### `post(string $endpoint, array $data = []): ApiResponseDto`

Send a POST request to the given endpoint.

```php
protected function post(string $endpoint, array $data = []): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$endpoint` | `string` | API endpoint (relative to base URL) |
| `$data` | `array<string, mixed>` | Request body data |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

### `put(string $endpoint, array $data = []): ApiResponseDto`

Send a PUT request to the given endpoint.

```php
protected function put(string $endpoint, array $data = []): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$endpoint` | `string` | API endpoint (relative to base URL) |
| `$data` | `array<string, mixed>` | Request body data |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

### `patch(string $endpoint, array $data = []): ApiResponseDto`

Send a PATCH request to the given endpoint.

```php
protected function patch(string $endpoint, array $data = []): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$endpoint` | `string` | API endpoint (relative to base URL) |
| `$data` | `array<string, mixed>` | Request body data |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

### `delete(string $endpoint): ApiResponseDto`

Send a DELETE request to the given endpoint.

```php
protected function delete(string $endpoint): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$endpoint` | `string` | API endpoint (relative to base URL) |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

### `request(string $method, string $endpoint, array $options = []): ApiResponseDto`

Send a request with the given method and options.

```php
protected function request(string $method, string $endpoint, array $options = []): ApiResponseDto
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$method` | `string` | HTTP method |
| `$endpoint` | `string` | API endpoint (relative to base URL) |
| `$options` | `array<string, mixed>` | Request options |

**Options array:**

| Key | Type | Description |
|-----|------|-------------|
| `query` | `array` | Query parameters |
| `body` | `array` | Form body data |
| `json` | `array` | JSON body data (sets Content-Type) |
| `headers` | `array` | Additional headers |

**Returns:** `ApiResponseDto`

**Throws:** `ApiRequestException` on transport failure, `ApiResponseException` on non-2xx status

---

## Full Example

```php
use TheWildFields\Tavriia\Http\AbstractApiProvider;
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;
use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;

final class StripeProvider extends AbstractApiProvider
{
    protected function configure(): ApiProviderConfigDto
    {
        return new ApiProviderConfigDto(
            baseUrl: 'https://api.stripe.com/v1',
            auth: new BearerTokenAuth(get_option('stripe_secret_key')),
            defaultHeaders: ['Content-Type' => 'application/x-www-form-urlencoded'],
            timeout: 30,
        );
    }

    public function createCustomer(string $email, string $name): array
    {
        return $this->post('/customers', [
            'email' => $email,
            'name' => $name,
        ])->json();
    }

    public function getCustomer(string $id): array
    {
        return $this->get("/customers/{$id}")->json();
    }

    public function listCustomers(int $limit = 10): array
    {
        return $this->get('/customers', ['limit' => $limit])->json();
    }

    public function deleteCustomer(string $id): array
    {
        return $this->delete("/customers/{$id}")->json();
    }
}
```

---

## See Also

- [API Provider guide](../api-provider.md)
- [`ApiProviderConfigDto`](api-provider-config-dto.md)
- [Auth Strategies](auth-strategies.md)
- [`HttpClient`](http-client.md)
