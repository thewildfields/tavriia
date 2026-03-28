<?php

declare(strict_types=1);

// Load Patchwork before anything else so its stream wrapper is active
// and can intercept all subsequently loaded files (including our stubs).
require_once __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';

// Load our WordPress stub functions AFTER Patchwork, so Patchwork's
// stream wrapper intercepts the file and can later redefine the functions.
require_once __DIR__ . '/wp-stubs.php';

// Load the Composer autoloader (Brain Monkey, PHPUnit, etc.)
require_once __DIR__ . '/../vendor/autoload.php';
