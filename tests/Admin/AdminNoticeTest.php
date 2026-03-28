<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests\Admin;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use TheWildFields\Tavriia\Admin\AdminNotice;
use TheWildFields\Tavriia\Tests\TestCase;

final class AdminNoticeTest extends TestCase
{
    // --- Named constructors ---

    public function test_success_sets_type_success(): void
    {
        $notice = AdminNotice::success('All good!');

        $this->assertSame(AdminNotice::TYPE_SUCCESS, $notice->type());
    }

    public function test_error_sets_type_error(): void
    {
        $notice = AdminNotice::error('Something broke.');

        $this->assertSame(AdminNotice::TYPE_ERROR, $notice->type());
    }

    public function test_warning_sets_type_warning(): void
    {
        $notice = AdminNotice::warning('Watch out.');

        $this->assertSame(AdminNotice::TYPE_WARNING, $notice->type());
    }

    public function test_info_sets_type_info(): void
    {
        $notice = AdminNotice::info('Just FYI.');

        $this->assertSame(AdminNotice::TYPE_INFO, $notice->type());
    }

    public function test_success_stores_message(): void
    {
        $notice = AdminNotice::success('Settings saved.');

        $this->assertSame('Settings saved.', $notice->message());
    }

    public function test_default_is_dismissible(): void
    {
        $notice = AdminNotice::success('Hi');

        $this->assertTrue($notice->isDismissible());
    }

    public function test_can_be_non_dismissible(): void
    {
        $notice = AdminNotice::error('Error', false);

        $this->assertFalse($notice->isDismissible());
    }

    // --- enqueue ---

    public function test_enqueue_calls_add_action_for_admin_notices(): void
    {
        Actions\expectAdded('admin_notices')->once();

        $notice = AdminNotice::success('Test message');
        $notice->enqueue();
    }

    public function test_enqueue_can_be_called_multiple_times(): void
    {
        Actions\expectAdded('admin_notices')->twice();

        $notice = AdminNotice::info('Multiple');
        $notice->enqueue();
        $notice->enqueue();
    }

    // --- render ---

    public function test_render_outputs_div_with_notice_class(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);
        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        $notice = AdminNotice::success('Saved!');

        ob_start();
        $notice->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="notice', $output);
    }

    public function test_render_includes_type_specific_class(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);
        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        $notice = AdminNotice::error('Oops');

        ob_start();
        $notice->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('notice-error', $output);
    }

    public function test_render_includes_is_dismissible_class_when_dismissible(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);
        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        $notice = AdminNotice::warning('Heads up', true);

        ob_start();
        $notice->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('is-dismissible', $output);
    }

    public function test_render_omits_is_dismissible_class_when_not_dismissible(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);
        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        $notice = AdminNotice::info('FYI', false);

        ob_start();
        $notice->render();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('is-dismissible', $output);
    }

    public function test_render_includes_message_in_paragraph(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);
        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        $notice = AdminNotice::success('Post created successfully.');

        ob_start();
        $notice->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('<p>Post created successfully.</p>', $output);
    }

    public function test_render_calls_esc_attr_on_type(): void
    {
        Functions\expect('esc_attr')
            ->once()
            ->with('success')
            ->andReturn('success');

        Functions\when('wp_kses_post')->alias(fn($v) => $v);

        ob_start();
        AdminNotice::success('Test')->render();
        ob_get_clean();
    }

    public function test_render_calls_wp_kses_post_on_message(): void
    {
        Functions\when('esc_attr')->alias(fn($v) => $v);

        Functions\expect('wp_kses_post')
            ->once()
            ->with('Safe <strong>message</strong>')
            ->andReturn('Safe <strong>message</strong>');

        ob_start();
        AdminNotice::info('Safe <strong>message</strong>')->render();
        ob_get_clean();
    }

    // --- Accessors ---

    public function test_message_accessor(): void
    {
        $notice = AdminNotice::success('Hello');

        $this->assertSame('Hello', $notice->message());
    }

    public function test_type_accessor(): void
    {
        $notice = AdminNotice::warning('Watch out');

        $this->assertSame('warning', $notice->type());
    }

    public function test_is_dismissible_accessor(): void
    {
        $notice = AdminNotice::error('Error', false);

        $this->assertFalse($notice->isDismissible());
    }

    // --- Type constants ---

    public function test_type_constants_have_correct_values(): void
    {
        $this->assertSame('success', AdminNotice::TYPE_SUCCESS);
        $this->assertSame('error', AdminNotice::TYPE_ERROR);
        $this->assertSame('warning', AdminNotice::TYPE_WARNING);
        $this->assertSame('info', AdminNotice::TYPE_INFO);
    }
}
