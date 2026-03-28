<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Http;

use TheWildFields\Tavriia\DTO\ApiRequestDTO;
use TheWildFields\Tavriia\Http\RequestBuilder;
use TheWildFields\Tavriia\Tests\TestCase;

final class RequestBuilderTest extends TestCase
{
    public function test_build_returns_api_request_dto(): void
    {
        $builder = new RequestBuilder('https://example.com');
        $dto     = $builder->build();

        $this->assertInstanceOf(ApiRequestDTO::class, $dto);
    }

    public function test_url_is_set_from_constructor(): void
    {
        $dto = (new RequestBuilder('https://api.example.com/v1'))->build();

        $this->assertSame('https://api.example.com/v1', $dto->url);
    }

    public function test_default_method_is_get(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->build();

        $this->assertSame('GET', $dto->method);
    }

    public function test_method_sets_uppercase_method(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->method('post')->build();

        $this->assertSame('POST', $dto->method);
    }

    public function test_method_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->method('DELETE');

        $this->assertNotSame($builder, $builder2);
        $this->assertSame('GET', $builder->build()->method);
        $this->assertSame('DELETE', $builder2->build()->method);
    }

    public function test_header_adds_single_header(): void
    {
        $dto = (new RequestBuilder('https://example.com'))
            ->header('Authorization', 'Bearer abc123')
            ->build();

        $this->assertSame(['Authorization' => 'Bearer abc123'], $dto->headers);
    }

    public function test_header_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->header('X-Foo', 'bar');

        $this->assertNotSame($builder, $builder2);
        $this->assertSame([], $builder->build()->headers);
        $this->assertArrayHasKey('X-Foo', $builder2->build()->headers);
    }

    public function test_header_can_be_chained_multiple_times(): void
    {
        $dto = (new RequestBuilder('https://example.com'))
            ->header('Accept', 'application/json')
            ->header('Authorization', 'Bearer token')
            ->build();

        $this->assertSame('application/json', $dto->headers['Accept']);
        $this->assertSame('Bearer token', $dto->headers['Authorization']);
    }

    public function test_headers_sets_all_headers_at_once(): void
    {
        $headers = ['X-A' => '1', 'X-B' => '2'];
        $dto     = (new RequestBuilder('https://example.com'))->headers($headers)->build();

        $this->assertSame($headers, $dto->headers);
    }

    public function test_body_sets_array_body(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->body(['key' => 'value'])->build();

        $this->assertSame(['key' => 'value'], $dto->body);
    }

    public function test_body_sets_string_body(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->body('raw string body')->build();

        $this->assertSame('raw string body', $dto->body);
    }

    public function test_body_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->body(['a' => 'b']);

        $this->assertNotSame($builder, $builder2);
        $this->assertSame([], $builder->build()->body);
    }

    public function test_json_body_encodes_data_as_json_string(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->jsonBody(['name' => 'Alice'])->build();

        $this->assertSame('{"name":"Alice"}', $dto->body);
    }

    public function test_json_body_sets_content_type_header(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->jsonBody(['a' => 1])->build();

        $this->assertSame('application/json', $dto->headers['Content-Type']);
    }

    public function test_json_body_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->jsonBody(['x' => 'y']);

        $this->assertNotSame($builder, $builder2);
        $this->assertSame([], $builder->build()->body);
    }

    public function test_timeout_sets_timeout(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->timeout(60)->build();

        $this->assertSame(60, $dto->timeout);
    }

    public function test_default_timeout_is_15(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->build();

        $this->assertSame(15, $dto->timeout);
    }

    public function test_timeout_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->timeout(30);

        $this->assertNotSame($builder, $builder2);
        $this->assertSame(15, $builder->build()->timeout);
        $this->assertSame(30, $builder2->build()->timeout);
    }

    public function test_ssl_verify_sets_false(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->sslVerify(false)->build();

        $this->assertFalse($dto->sslVerify);
    }

    public function test_default_ssl_verify_is_true(): void
    {
        $dto = (new RequestBuilder('https://example.com'))->build();

        $this->assertTrue($dto->sslVerify);
    }

    public function test_ssl_verify_is_immutable(): void
    {
        $builder  = new RequestBuilder('https://example.com');
        $builder2 = $builder->sslVerify(false);

        $this->assertNotSame($builder, $builder2);
        $this->assertTrue($builder->build()->sslVerify);
        $this->assertFalse($builder2->build()->sslVerify);
    }

    public function test_with_args_merges_extra_args(): void
    {
        $dto = (new RequestBuilder('https://example.com'))
            ->withArgs(['blocking' => false])
            ->build();

        $this->assertSame(['blocking' => false], $dto->extraArgs);
    }

    public function test_with_args_accumulates_on_multiple_calls(): void
    {
        $dto = (new RequestBuilder('https://example.com'))
            ->withArgs(['blocking' => false])
            ->withArgs(['redirection' => 5])
            ->build();

        $this->assertFalse($dto->extraArgs['blocking']);
        $this->assertSame(5, $dto->extraArgs['redirection']);
    }

    public function test_full_fluent_chain_builds_correct_dto(): void
    {
        $dto = (new RequestBuilder('https://api.example.com'))
            ->method('POST')
            ->header('Authorization', 'Bearer secret')
            ->jsonBody(['event' => 'purchase'])
            ->timeout(30)
            ->sslVerify(false)
            ->build();

        $this->assertSame('https://api.example.com', $dto->url);
        $this->assertSame('POST', $dto->method);
        $this->assertSame('Bearer secret', $dto->headers['Authorization']);
        $this->assertSame('application/json', $dto->headers['Content-Type']);
        $this->assertSame('{"event":"purchase"}', $dto->body);
        $this->assertSame(30, $dto->timeout);
        $this->assertFalse($dto->sslVerify);
    }
}
