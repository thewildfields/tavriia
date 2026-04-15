---
title: API Provider
description: Define external API services with base URL, authentication, and default settings
sidebar_position: 8
---

# API Provider

Tavriia provides an API Provider abstraction for defining external API services. Instead of configuring base URL, authentication, and headers on every request, you define them once in a provider class.

---

## Classes

| Class | Role |
|-------|------|
| `AbstractApiProvider` | Base class for API providers |
| `ApiProviderConfigDto` | Immutable provider configuration |
| `AuthStrategyInterface` | Contract for authentication strategies |
| `BearerTokenAuth` | Bearer token authentication |
| `ApiKeyAuth` | Header-based API key authentication |
| `BasicAuth` | HTTP Basic authentication |
| `QueryParamAuth` | Query parameter authentication |
| `NoAuth` | No authentication (default) |

---

## Quick Start

### 1. Create a Provider

Extend `AbstractApiProvider` and implement the `configure()` method:

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
        );
    }

    public function createPaymentIntent(int $amount, string $currency): array
    {
        return $this->post('/payment_intents', [
            'amount' => $amount,
            'currency' => $currency,
        ])->json();
    }

    public function getPaymentIntent(string $id): array
    {
        return $this->get("/payment_intents/{$id}")->json();
    }
}
```

### 2. Use the Provider

```php
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;

$client = new HttpClient(new ResponseProcessor());
$stripe = new StripeProvider($client);

$intent = $stripe->createPaymentIntent(2000, 'usd');
```

---

## Authentication Strategies

### Bearer Token

For APIs that use `Authorization: Bearer <token>`:

```php
use TheWildFields\Tavriia\Http\Auth\BearerTokenAuth;

new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com',
    auth: new BearerTokenAuth($token),
);
```

### API Key Header

For APIs that use a custom header like `X-API-Key`:

```php
use TheWildFields\Tavriia\Http\Auth\ApiKeyAuth;

new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com',
    auth: new ApiKeyAuth($apiKey, 'X-API-Key'),
);
```

### HTTP Basic Auth

For APIs that use HTTP Basic authentication:

```php
use TheWildFields\Tavriia\Http\Auth\BasicAuth;

new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com',
    auth: new BasicAuth($username, $password),
);
```

### Query Parameter

For APIs that require the key in the URL (like Google APIs):

```php
use TheWildFields\Tavriia\Http\Auth\QueryParamAuth;

new ApiProviderConfigDto(
    baseUrl: 'https://maps.googleapis.com/maps/api',
    auth: new QueryParamAuth($apiKey, 'key'),
);
// Requests will include ?key=<apiKey>
```

### No Authentication

For public APIs (this is the default):

```php
use TheWildFields\Tavriia\Http\Auth\NoAuth;

new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com',
    auth: new NoAuth(), // optional, this is the default
);
```

---

## Provider Configuration

`ApiProviderConfigDto` accepts these options:

```php
new ApiProviderConfigDto(
    baseUrl: 'https://api.example.com',       // Required
    auth: new BearerTokenAuth($token),        // Default: NoAuth
    defaultHeaders: ['Accept' => 'application/json'], // Default: []
    timeout: 30,                              // Default: 15 seconds
    sslVerify: true,                          // Default: true
);
```

---

## Available HTTP Methods

Inside your provider, use these protected methods:

```php
// GET request with query parameters
$this->get('/endpoint', ['page' => 1, 'limit' => 10]);

// POST with form data
$this->post('/endpoint', ['name' => 'value']);

// PUT with form data
$this->put('/endpoint', ['name' => 'value']);

// PATCH with form data
$this->patch('/endpoint', ['name' => 'value']);

// DELETE
$this->delete('/endpoint');

// Custom request with full options
$this->request('POST', '/endpoint', [
    'query'   => ['expand' => 'items'],
    'json'    => ['name' => 'value'],  // JSON body
    'headers' => ['X-Custom' => 'value'],
]);
```

---

## Error Handling

Providers throw the same exceptions as `HttpClient`:

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

try {
    $result = $stripe->createPaymentIntent(2000, 'usd');
} catch (ApiRequestException $e) {
    // Network failure (timeout, DNS, SSL)
} catch (ApiResponseException $e) {
    // Server returned 4xx or 5xx
    $response = $e->getResponse();
    $error = $response->json();
}
```

---

## Full Example: Google Places Provider

```php
use TheWildFields\Tavriia\Http\AbstractApiProvider;
use TheWildFields\Tavriia\Http\Auth\QueryParamAuth;
use TheWildFields\Tavriia\Dto\ApiProviderConfigDto;

final class GooglePlacesProvider extends AbstractApiProvider
{
    protected function configure(): ApiProviderConfigDto
    {
        return new ApiProviderConfigDto(
            baseUrl: 'https://maps.googleapis.com/maps/api/place',
            auth: new QueryParamAuth(get_option('google_api_key'), 'key'),
            defaultHeaders: ['Accept' => 'application/json'],
            timeout: 30,
        );
    }

    public function findPlace(string $query, array $fields = []): array
    {
        return $this->get('/findplacefromtext/json', [
            'input' => $query,
            'inputtype' => 'textquery',
            'fields' => implode(',', $fields ?: ['place_id', 'name', 'formatted_address']),
        ])->json();
    }

    public function getPlaceDetails(string $placeId, array $fields = []): array
    {
        return $this->get('/details/json', [
            'place_id' => $placeId,
            'fields' => implode(',', $fields ?: ['name', 'formatted_address', 'geometry']),
        ])->json();
    }
}
```

---

## API Reference

- [`AbstractApiProvider`](api-reference/abstract-api-provider.md)
- [`ApiProviderConfigDto`](api-reference/api-provider-config-dto.md)
- [`AuthStrategyInterface`](api-reference/auth-strategies.md#authstrategyinterface)
- [`BearerTokenAuth`](api-reference/auth-strategies.md#bearertokenauth)
- [`ApiKeyAuth`](api-reference/auth-strategies.md#apikeyauth)
- [`BasicAuth`](api-reference/auth-strategies.md#basicauth)
- [`QueryParamAuth`](api-reference/auth-strategies.md#queryparamauth)
- [`NoAuth`](api-reference/auth-strategies.md#noauth)
