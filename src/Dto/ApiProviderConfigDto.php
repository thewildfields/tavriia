<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Dto;

use TheWildFields\Tavriia\Http\Auth\AuthStrategyInterface;
use TheWildFields\Tavriia\Http\Auth\NoAuth;

/**
 * Immutable configuration for an API provider.
 */
final readonly class ApiProviderConfigDto
{
    /**
     * @param string                       $baseUrl        Base URL for all requests (without trailing slash).
     * @param AuthStrategyInterface        $auth           Authentication strategy.
     * @param array<string, string>        $defaultHeaders Default headers for all requests.
     * @param int                          $timeout        Request timeout in seconds.
     * @param bool                         $sslVerify      Whether to verify SSL certificates.
     */
    public function __construct(
        public string $baseUrl,
        public AuthStrategyInterface $auth = new NoAuth(),
        public array $defaultHeaders = [],
        public int $timeout = 15,
        public bool $sslVerify = true,
    ) {}
}
