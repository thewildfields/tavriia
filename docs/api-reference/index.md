---
title: API Reference
description: Complete method-level reference for all Tavriia classes
sidebar_position: 10
---

# API Reference

Complete method-level documentation for every class in Tavriia.

---

## Module System

| Class/Interface | Description |
|-----------------|-------------|
| [`AbstractModule`](abstract-module.md) | Base class for all plugin modules |
| [`HasHooksInterface`](contracts.md#hashooksinterface) | Contract for hook-registering classes |

---

## Post Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`PostFactory`](post-factory.md) | Create, update, and delete posts |
| [`PostRepository`](post-repository.md) | Read posts by ID or query |
| [`MetaManager`](meta-manager.md) | Typed post meta accessors |
| [`PostRepositoryInterface`](contracts.md#postrepositoryinterface) | Contract for post repositories |
| [`PostDTO`](post-dto.md) | Post data transfer object |
| [`PostNotFoundException`](exceptions.md#postnotfoundexception) | Thrown when a post operation fails |

---

## Taxonomy Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`TermFactory`](term-factory.md) | Create, update, delete terms |
| [`TermRepository`](term-repository.md) | Read terms by ID, field, or object |
| [`TermMetaManager`](term-meta-manager.md) | Typed term meta accessors |
| [`TaxonomyRepositoryInterface`](contracts.md#taxonomyrepositoryinterface) | Contract for taxonomy repositories |
| [`TaxonomyDTO`](taxonomy-dto.md) | Term data transfer object |
| [`TermNotFoundException`](exceptions.md#termnotfoundexception) | Thrown when a term operation fails |

---

## Query Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`QueryBuilder`](query-builder.md) | Fluent post query builder |
| [`QueryResult`](query-result.md) | Typed, iterable query result |
| [`QueryBuilderInterface`](contracts.md#querybuilderinterface) | Contract for query builders |
| [`QueryArgsDTO`](query-args-dto.md) | Query arguments data transfer object |

---

## HTTP Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`HttpClient`](http-client.md) | Execute HTTP requests |
| [`RequestBuilder`](request-builder.md) | Fluent HTTP request builder |
| [`ResponseProcessor`](response-processor.md) | Convert raw WP responses to DTOs |
| [`ApiClientInterface`](contracts.md#apiclientinterface) | Contract for HTTP clients |
| [`ApiRequestDTO`](api-request-dto.md) | HTTP request data transfer object |
| [`ApiResponseDTO`](api-response-dto.md) | HTTP response data transfer object |
| [`ApiRequestException`](exceptions.md#apirequestexception) | Thrown on transport-level failure |
| [`ApiResponseException`](exceptions.md#apiresponseexception) | Thrown on non-2xx HTTP response |

---

## API Provider Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`AbstractApiProvider`](abstract-api-provider.md) | Base class for API providers |
| [`ApiProviderConfigDto`](api-provider-config-dto.md) | Provider configuration DTO |
| [`AuthStrategyInterface`](auth-strategies.md#authstrategyinterface) | Contract for auth strategies |
| [`BearerTokenAuth`](auth-strategies.md#bearertokenauth) | Bearer token authentication |
| [`ApiKeyAuth`](auth-strategies.md#apikeyauth) | Header-based API key auth |
| [`BasicAuth`](auth-strategies.md#basicauth) | HTTP Basic authentication |
| [`QueryParamAuth`](auth-strategies.md#queryparamauth) | Query parameter authentication |
| [`NoAuth`](auth-strategies.md#noauth) | No authentication (default) |

---

## REST API Layer

| Class/Interface | Description |
|-----------------|-------------|
| [`RestServer`](rest-server.md) | Register REST routes with WordPress |
| [`RestRouteBuilder`](rest-route-builder.md) | Fluent REST route builder |
| [`RestRouteDto`](rest-route-dto.md) | REST route data transfer object |
| [`RestResponse`](rest-response.md) | Typed REST response object |
| [`AbstractRestController`](abstract-rest-controller.md) | Base class for REST controllers |
| [`RestServerInterface`](contracts.md#restserverinterface) | Contract for REST servers |
| [`RestRouteRegistrationException`](exceptions.md#restrouteregistrationexception) | Thrown when route registration fails |

---

## Admin Layer

| Class | Description |
|-------|-------------|
| [`AdminMenuPage`](admin-menu-page.md) | Register admin menu pages |
| [`AdminNotice`](admin-notice.md) | Display admin notices |

---

## Shared

| Class | Description |
|-------|-------------|
| [`AbstractMetaManager`](meta-manager.md#abstractmetamanager) | Shared typed meta accessor base class |
| [All Contracts](contracts.md) | All framework interfaces |
| [All Exceptions](exceptions.md) | All framework exceptions |
