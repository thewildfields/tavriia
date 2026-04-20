<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Rest;

use TheWildFields\Tavriia\Rest\RestResponse;
use TheWildFields\Tavriia\Tests\TestCase;
use WP_Error;
use WP_REST_Response;

final class RestResponseTest extends TestCase
{
    // --- ok ---

    public function test_ok_status_is_200(): void
    {
        $this->assertSame(200, RestResponse::ok(['id' => 1])->status());
    }

    public function test_ok_stores_data(): void
    {
        $response = RestResponse::ok(['id' => 1, 'title' => 'Hello']);

        $this->assertSame(['id' => 1, 'title' => 'Hello'], $response->data());
    }

    public function test_ok_accepts_null_data(): void
    {
        $this->assertNull(RestResponse::ok()->data());
    }

    public function test_ok_stores_headers(): void
    {
        $response = RestResponse::ok(['x' => 1], ['X-Total-Count' => '42']);

        $this->assertSame(['X-Total-Count' => '42'], $response->headers());
    }

    public function test_ok_is_not_error(): void
    {
        $this->assertFalse(RestResponse::ok(['x' => 1])->isError());
    }

    // --- created ---

    public function test_created_status_is_201(): void
    {
        $this->assertSame(201, RestResponse::created(['id' => 1])->status());
    }

    public function test_created_stores_data(): void
    {
        $this->assertSame(['id' => 1], RestResponse::created(['id' => 1])->data());
    }

    // --- noContent ---

    public function test_no_content_status_is_204(): void
    {
        $this->assertSame(204, RestResponse::noContent()->status());
    }

    public function test_no_content_data_is_null(): void
    {
        $this->assertNull(RestResponse::noContent()->data());
    }

    // --- error ---

    public function test_error_marks_response_as_error(): void
    {
        $this->assertTrue(RestResponse::error('bad', 'Bad things happened')->isError());
    }

    public function test_error_stores_code_and_message(): void
    {
        $response = RestResponse::error('invalid_input', 'Invalid input', 422);

        $this->assertSame('invalid_input', $response->errorCode());
        $this->assertSame('Invalid input', $response->errorMessage());
        $this->assertSame(422, $response->status());
    }

    public function test_error_default_status_is_400(): void
    {
        $this->assertSame(400, RestResponse::error('bad', 'msg')->status());
    }

    public function test_error_stores_error_data(): void
    {
        $response = RestResponse::error('bad', 'msg', 400, ['field' => 'title']);

        $this->assertSame(['field' => 'title'], $response->errorData());
    }

    // --- Error shortcuts ---

    public function test_bad_request_status_is_400(): void
    {
        $this->assertSame(400, RestResponse::badRequest('bad', 'nope')->status());
    }

    public function test_unauthorized_status_is_401(): void
    {
        $this->assertSame(401, RestResponse::unauthorized('auth', 'nope')->status());
    }

    public function test_forbidden_status_is_403(): void
    {
        $this->assertSame(403, RestResponse::forbidden('forbidden', 'nope')->status());
    }

    public function test_not_found_status_is_404(): void
    {
        $this->assertSame(404, RestResponse::notFound('missing', 'nope')->status());
    }

    public function test_server_error_status_is_500(): void
    {
        $this->assertSame(500, RestResponse::serverError('boom', 'nope')->status());
    }

    public function test_all_error_shortcuts_are_errors(): void
    {
        $this->assertTrue(RestResponse::badRequest('a', 'b')->isError());
        $this->assertTrue(RestResponse::unauthorized('a', 'b')->isError());
        $this->assertTrue(RestResponse::forbidden('a', 'b')->isError());
        $this->assertTrue(RestResponse::notFound('a', 'b')->isError());
        $this->assertTrue(RestResponse::serverError('a', 'b')->isError());
    }

    // --- toWp() - success path ---

    public function test_to_wp_for_ok_returns_wp_rest_response(): void
    {
        $wp = RestResponse::ok(['id' => 1])->toWp();

        $this->assertInstanceOf(WP_REST_Response::class, $wp);
    }

    public function test_to_wp_for_ok_includes_data(): void
    {
        $wp = RestResponse::ok(['id' => 1])->toWp();

        $this->assertSame(['id' => 1], $wp->get_data());
    }

    public function test_to_wp_for_ok_includes_status(): void
    {
        $wp = RestResponse::created(['id' => 1])->toWp();

        $this->assertSame(201, $wp->get_status());
    }

    public function test_to_wp_for_ok_adds_headers(): void
    {
        $wp = RestResponse::ok(['x' => 1], ['X-Total-Count' => '42'])->toWp();

        $this->assertSame(['X-Total-Count' => '42'], $wp->get_headers());
    }

    // --- toWp() - error path ---

    public function test_to_wp_for_error_returns_wp_error(): void
    {
        $wp = RestResponse::error('code', 'message')->toWp();

        $this->assertInstanceOf(WP_Error::class, $wp);
    }

    public function test_to_wp_for_error_carries_code(): void
    {
        $wp = RestResponse::error('invalid', 'Invalid input')->toWp();

        $this->assertSame('invalid', $wp->get_error_code());
    }

    public function test_to_wp_for_error_carries_message(): void
    {
        $wp = RestResponse::error('invalid', 'Invalid input')->toWp();

        $this->assertSame('Invalid input', $wp->get_error_message());
    }

    public function test_to_wp_for_error_includes_status_in_data(): void
    {
        $wp = RestResponse::notFound('missing', 'Not here')->toWp();

        $data = $wp->get_error_data();

        $this->assertIsArray($data);
        $this->assertSame(404, $data['status']);
    }

    public function test_to_wp_for_error_includes_extra_error_data(): void
    {
        $wp = RestResponse::error('invalid', 'msg', 400, ['field' => 'title'])->toWp();

        $data = $wp->get_error_data();

        $this->assertSame('title', $data['field']);
        $this->assertSame(400, $data['status']);
    }
}
