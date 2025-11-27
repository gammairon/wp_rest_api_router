<?php
/**
 * Handles permission middleware pipeline building for REST API routes.
 *
 * This builder collects all permission middlewares from the namespace group,
 * route, and action, and composes them into a single pipeline callable.
 * Each middleware receives the WP_REST_Request and a $next callable.
 * If any middleware denies access (returns FALSE or WP_Error), the pipeline stops.
 *
 * Example usage:
 * ```php
 * $builderDTO = new BuilderDTO($action, $route, $group);
 * $permissionBuilder = new PermissionBuilder($builderDTO);
 * $isAllowed = $permissionBuilder($request); // TRUE or FALSE
 * ```
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Builders;

use gi_api_route\Abstract\BaseBuilder;
use gi_api_route\Contracts\PermissionMiddlewareInterface;
use gi_api_route\Enums\MiddlewareType;
use gi_api_route\Support\Logger;
use WP_REST_Request;
use Exception;

final class PermissionBuilder extends BaseBuilder
{

    /**
     * Build the permission middleware pipeline.
     *
     * This method constructs a "pipe" of all permission middlewares:
     * each middleware wraps the previous one, forming a chain.
     * The first middleware receives the request and calls $next if allowed.
     *
     * @return callable Returns a callable that accepts WP_REST_Request and returns
     *                  TRUE (allowed) or FALSE/WP_Error (denied).
     */
    public function build(): callable
    {

        $permissionMiddlewares = $this->getMiddlewares(MiddlewareType::PERMISSION);

        // No permission middlewares → allow access by default
        if (empty($permissionMiddlewares)) {
            return [$this, 'defaultHandler'];
        }

        /**
         * Pipeline construction:
         * Middlewares are reversed because every next middleware
         * should wrap the previously constructed callable.
         */
        $pipeline = array_reduce(
            array_reverse($permissionMiddlewares),
            /**
             * @param callable $next Previously built part of the pipeline
             * @param PermissionMiddlewareInterface $middleware Current middleware to wrap
             * @return callable
             */
            static function(callable $next, PermissionMiddlewareInterface $middleware) {

                return static function(WP_REST_Request $request) use ($next, $middleware) {
                    try {

                        // Call middleware handler
                        return $middleware->handle($request, $next);

                    } catch (Exception $e) {
                        //return new WP_Error($e->getCode(), $e->getMessage());
                        // Otherwise continue the chain
                        return false;
                    }
                };
            },
            // Initial callback for the pipeline (final step)
            [$this, 'defaultHandler']
        );



        return $this->cached($pipeline);
    }


    /**
     * Default permission handler.
     *
     * Returns TRUE, meaning "access allowed".
     * Used when no permission middlewares are defined.
     *
     * @param WP_REST_Request $request
     * @return bool Always TRUE
     */
    public function defaultHandler(WP_REST_Request $request): bool
    {
        return true;
    }

    /**
     * Wraps a pipeline with caching per user and route.
     *
     * @param callable $pipeline Middleware pipeline
     * @return callable Cached pipeline callable
     */
    private function cached(callable $pipeline): callable
    {
        static $cache = [];

        return static function(WP_REST_Request $request) use ($pipeline, &$cache) {
            $key = md5(sprintf(
                '%s:%s:%d',
                $request->get_method(),
                $request->get_route(),
                get_current_user_id()
            ));

            if (isset($cache[$key])) {
                Logger::write('PermissionBuilder FROM cache');
                return $cache[$key];
            }
            Logger::write('PermissionBuilder NO cache');
            $result = $pipeline($request);
            $cache[$key] = $result;

            return $result;
        };
    }
}
