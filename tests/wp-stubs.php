<?php

/**
 * WordPress function stubs with correct named-parameter signatures.
 *
 * This file is required AFTER Patchwork loads so Patchwork can intercept
 * and redefine these functions in tests. The parameter names must match
 * what the source code passes as named arguments.
 */

if (!function_exists('add_menu_page')) {
    function add_menu_page(
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        ?callable $callback = null,
        string $icon_url = '',
        int|float|null $position = null,
    ): string {
        return '';
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page(
        string $parent_slug,
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        ?callable $callback = null,
        ?int $position = null,
    ): string|false {
        return false;
    }
}

if (!function_exists('register_rest_route')) {
    function register_rest_route(
        string $route_namespace,
        string $route,
        array $args = [],
        bool $override = false,
    ): bool {
        return true;
    }
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        /** @var mixed */
        public $data;
        public int $status;
        /** @var array<string, string> */
        public array $headers = [];

        public function __construct($data = null, int $status = 200, array $headers = [])
        {
            $this->data    = $data;
            $this->status  = $status;
            $this->headers = $headers;
        }

        public function get_data()
        {
            return $this->data;
        }

        public function get_status(): int
        {
            return $this->status;
        }

        public function get_headers(): array
        {
            return $this->headers;
        }

        public function header(string $name, string $value): void
        {
            $this->headers[$name] = $value;
        }
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        /** @var array<string, array<int, string>> */
        private array $errors = [];
        /** @var array<string, mixed> */
        private array $error_data = [];

        public function __construct(string $code = '', string $message = '', $data = '')
        {
            if ($code !== '') {
                $this->errors[$code][] = $message;
                if ($data !== '') {
                    $this->error_data[$code] = $data;
                }
            }
        }

        public function get_error_code(): string
        {
            $codes = array_keys($this->errors);

            return $codes[0] ?? '';
        }

        public function get_error_message(string $code = ''): string
        {
            if ($code === '') {
                $code = $this->get_error_code();
            }

            return $this->errors[$code][0] ?? '';
        }

        /**
         * @return mixed
         */
        public function get_error_data(string $code = '')
        {
            if ($code === '') {
                $code = $this->get_error_code();
            }

            return $this->error_data[$code] ?? null;
        }
    }
}
