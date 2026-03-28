---
title: HTTP Client
description: Make external HTTP requests using WordPress's HTTP API with typed responses
sidebar_position: 7
---

# HTTP Client

Tavriia wraps WordPress's HTTP API (`wp_remote_get`, `wp_remote_post`, `wp_remote_request`) with typed request/response objects and automatic error conversion.

---

## Classes

| Class | Role |
|-------|------|
| `HttpClient` | Executes HTTP requests |
| `RequestBuilder` | Fluent builder for `ApiRequestDTO` |
| `ResponseProcessor` | Converts raw WordPress responses to `ApiResponseDTO` |

---

## Quick Start

### Simple GET Request

```php
use TheWildFields\Tavriia\Http\HttpClient;
use TheWildFields\Tavriia\Http\ResponseProcessor;

$client = new HttpClient(new ResponseProcessor());

$response = $client->get('https://api.example.com/events');
$data = $response->json(); // decoded array/object
```

### Simple POST Request

```php
$response = $client->post('https://api.example.com/events', [
    'body'    => json_encode(['name' => 'Jazz Festival']),
    'headers' => ['Content-Type' => 'application/json'],
]);
```

---

## Building Requests with RequestBuilder

For anything more complex, use `RequestBuilder` for a fluent, readable request definition:

```php
use TheWildFields\Tavriia\Http\RequestBuilder;

$request = (new RequestBuilder('https://api.example.com/events'))
    ->method('POST')
    ->header('Authorization', 'Bearer ' . $apiKey)
    ->header('Accept', 'application/json')
    ->jsonBody(['name' => 'Jazz Festival', 'date' => '2026-07-15'])
    ->timeout(30)
    ->build();

$response = $client->request($request);
```

`jsonBody()` sets the body as a JSON-encoded string and automatically adds the `Content-Type: application/json` header.

### All RequestBuilder Methods

```php
(new RequestBuilder($url))
    ->method('PUT')                          // default: 'GET'
    ->header('X-Custom', 'value')           // add single header
    ->headers(['X-A' => 'a', 'X-B' => 'b']) // add multiple headers
    ->body('raw string body')               // raw body
    ->jsonBody(['key' => 'value'])           // JSON body + Content-Type header
    ->timeout(60)                           // seconds, default: 15
    ->sslVerify(false)                      // default: true
    ->withArgs(['redirection' => 0])        // extra WP HTTP API args
    ->build();                              // returns ApiRequestDTO
```

`RequestBuilder` is immutable — every setter returns a cloned instance.

---

## ApiResponseDTO

All responses are returned as `ApiResponseDTO`:

```php
$response->statusCode;   // int — e.g. 200, 404, 500
$response->body;         // string — raw response body
$response->headers;      // array — response headers

$response->json();           // mixed — decoded JSON, throws JsonException on invalid JSON
$response->isSuccess();      // bool — true for 2xx
$response->isClientError();  // bool — true for 4xx
$response->isServerError();  // bool — true for 5xx
```

---

## Error Handling

### WP_Error (Network Failures)

If WordPress cannot reach the server (DNS failure, timeout, SSL error), `ResponseProcessor` throws `ApiRequestException`:

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;

try {
    $response = $client->get('https://api.example.com/events');
} catch (ApiRequestException $e) {
    // Network-level failure — log and fall back
    error_log('API request failed: ' . $e->getMessage());
}
```

### Non-2xx Responses

If the server responds with a 4xx or 5xx status code, `ResponseProcessor` throws `ApiResponseException`:

```php
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

try {
    $response = $client->get('https://api.example.com/events');
} catch (ApiResponseException $e) {
    $failedResponse = $e->getResponse();
    error_log("HTTP {$failedResponse->statusCode}: {$failedResponse->body}");
}
```

### Handling Both

```php
use TheWildFields\Tavriia\Exceptions\ApiRequestException;
use TheWildFields\Tavriia\Exceptions\ApiResponseException;

try {
    $response = $client->get($url);
    $data = $response->json();
} catch (ApiRequestException $e) {
    // Transport failure (timeout, DNS, SSL)
} catch (ApiResponseException $e) {
    // Server returned 4xx or 5xx
} catch (\JsonException $e) {
    // Response body was not valid JSON
}
```

---

## Injecting HttpClient

Type-hint against the `ApiClientInterface` in your modules for testability:

```php
use TheWildFields\Tavriia\Contracts\ApiClientInterface;

final class SyncModule extends AbstractModule
{
    public function __construct(
        private readonly ApiClientInterface $apiClient,
        private readonly PostFactory $postFactory,
    ) {}
}
```

In production, pass `new HttpClient(new ResponseProcessor())`. In tests, pass a mock.

---

## ApiRequestDTO

If you need to inspect or serialize the request before sending it:

```php
$request = (new RequestBuilder($url))
    ->header('Authorization', 'Bearer ' . $token)
    ->jsonBody($payload)
    ->build();

// Inspect
echo $request->url;
echo $request->method;
print_r($request->headers);

// Convert to WP-compatible args
$wpArgs = $request->toWpArgs();
```

---

## API Reference

- [`HttpClient`](api-reference/http-client.md)
- [`RequestBuilder`](api-reference/request-builder.md)
- [`ResponseProcessor`](api-reference/response-processor.md)
- [`ApiRequestDTO`](api-reference/api-request-dto.md)
- [`ApiResponseDTO`](api-reference/api-response-dto.md)
- [`ApiRequestException`](api-reference/exceptions.md#apirequestexception)
- [`ApiResponseException`](api-reference/exceptions.md#apiresponseexception)
