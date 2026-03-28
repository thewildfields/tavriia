<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Tests;

use TheWildFields\Tavriia\AbstractModule;
use TheWildFields\Tavriia\Contracts\HasHooksInterface;

final class AbstractModuleTest extends TestCase
{
    private function makeConcreteModule(callable $onRegisterHooks = null): AbstractModule
    {
        return new class ($onRegisterHooks) extends AbstractModule {
            private $callback;

            public function __construct(?callable $callback)
            {
                $this->callback = $callback;
            }

            public function register_hooks(): void
            {
                if ($this->callback !== null) {
                    ($this->callback)();
                }
            }
        };
    }

    public function test_boot_calls_register_hooks(): void
    {
        $called = false;

        $module = $this->makeConcreteModule(function () use (&$called): void {
            $called = true;
        });

        $module->boot();

        $this->assertTrue($called);
    }

    public function test_abstract_module_implements_has_hooks_interface(): void
    {
        $module = $this->makeConcreteModule();

        $this->assertInstanceOf(HasHooksInterface::class, $module);
    }

    public function test_register_hooks_is_called_once_per_boot(): void
    {
        $callCount = 0;

        $module = $this->makeConcreteModule(function () use (&$callCount): void {
            $callCount++;
        });

        $module->boot();

        $this->assertSame(1, $callCount);
    }

    public function test_boot_can_be_called_multiple_times(): void
    {
        $callCount = 0;

        $module = $this->makeConcreteModule(function () use (&$callCount): void {
            $callCount++;
        });

        $module->boot();
        $module->boot();

        $this->assertSame(2, $callCount);
    }

    public function test_module_is_instance_of_abstract_module(): void
    {
        $module = $this->makeConcreteModule();

        $this->assertInstanceOf(AbstractModule::class, $module);
    }

    public function test_register_hooks_is_abstract_and_can_add_actions(): void
    {
        // Verify that register_hooks receives the call during boot and can invoke
        // WordPress hooks (Brain Monkey captures these without running actual WP).
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);

        $module = new class extends AbstractModule {
            public array $registered = [];

            public function register_hooks(): void
            {
                add_action('init', [$this, 'init']);
                $this->registered[] = 'init';
            }

            public function init(): void
            {
            }
        };

        $module->boot();

        $this->assertContains('init', $module->registered);
    }
}
