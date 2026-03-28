<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Admin;

/**
 * Typed wrapper for displaying WordPress admin notices.
 *
 * Queues notices via the admin_notices hook so plugin code never
 * directly echoes HTML or hooks into admin_notices manually.
 *
 * Usage:
 *   AdminNotice::success('Settings saved.')->enqueue();
 *   AdminNotice::error('Something went wrong.')->enqueue();
 */
final class AdminNotice
{
    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR   = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO    = 'info';

    private function __construct(
        private readonly string $message,
        private readonly string $type,
        private readonly bool $isDismissible,
    ) {}

    // -----------------------------------------------------------------
    // Named constructors
    // -----------------------------------------------------------------

    public static function success(string $message, bool $isDismissible = true): self
    {
        return new self($message, self::TYPE_SUCCESS, $isDismissible);
    }

    public static function error(string $message, bool $isDismissible = true): self
    {
        return new self($message, self::TYPE_ERROR, $isDismissible);
    }

    public static function warning(string $message, bool $isDismissible = true): self
    {
        return new self($message, self::TYPE_WARNING, $isDismissible);
    }

    public static function info(string $message, bool $isDismissible = true): self
    {
        return new self($message, self::TYPE_INFO, $isDismissible);
    }

    // -----------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------

    /**
     * Enqueue the notice for output on the next admin_notices action.
     *
     * Safe to call multiple times; each call registers a separate closure.
     */
    public function enqueue(): void
    {
        add_action('admin_notices', function (): void {
            $this->render();
        });
    }

    /**
     * Render the notice HTML immediately.
     *
     * Only call this directly inside an admin_notices callback.
     * Prefer enqueue() in all other contexts.
     */
    public function render(): void
    {
        $dismissibleClass = $this->isDismissible ? ' is-dismissible' : '';
        $type             = esc_attr($this->type);
        $message          = wp_kses_post($this->message);

        echo '<div class="notice notice-' . $type . $dismissibleClass . '">'
            . '<p>' . $message . '</p>'
            . '</div>';
    }

    // -----------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------

    public function message(): string
    {
        return $this->message;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isDismissible(): bool
    {
        return $this->isDismissible;
    }
}
