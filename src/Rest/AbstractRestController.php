<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Rest;

use TheWildFields\Tavriia\Contracts\HasHooksInterface;
use TheWildFields\Tavriia\Contracts\RestServerInterface;
use TheWildFields\Tavriia\Dto\RestRouteDto;

/**
 * Abstract base class for REST API controllers.
 *
 * Consuming plugins extend this class to group related REST endpoints.
 * Implementations declare their routes via the routes() method; the
 * framework handles hooking into rest_api_init and registering them
 * through the injected RestServer.
 *
 * Example:
 *
 *   final class EventsController extends AbstractRestController
 *   {
 *       public function __construct(
 *           RestServer $server,
 *           private readonly PostRepository $repository,
 *       ) {
 *           parent::__construct($server);
 *       }
 *
 *       protected function routes(): iterable
 *       {
 *           yield (new RestRouteBuilder('my-plugin/v1', '/events'))
 *               ->get([$this, 'list'])
 *               ->public()
 *               ->build();
 *
 *           yield (new RestRouteBuilder('my-plugin/v1', '/events/(?P<id>\d+)'))
 *               ->get([$this, 'show'])
 *               ->public()
 *               ->arg('id', ['type' => 'integer', 'required' => true])
 *               ->build();
 *       }
 *
 *       public function list(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
 *       {
 *           return RestResponse::ok($this->repository->findMany($args))->toWp();
 *       }
 *   }
 */
abstract class AbstractRestController implements HasHooksInterface
{
    public function __construct(
        private readonly RestServerInterface $server,
    ) {}

    /**
     * Register the rest_api_init callback.
     *
     * Subclasses must not override this method; override routes() instead.
     */
    final public function register_hooks(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register every route returned by routes() with the REST server.
     *
     * This method is public so WordPress can invoke it as a hook callback;
     * plugin code should not call it directly.
     */
    final public function registerRoutes(): void
    {
        $this->server->registerMany($this->routes());
    }

    /**
     * Return the route definitions this controller exposes.
     *
     * Implementations can return an array or use a generator (yield).
     *
     * @return iterable<RestRouteDto>
     */
    abstract protected function routes(): iterable;
}
