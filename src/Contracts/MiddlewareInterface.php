<?php
/**
 * Base interface for all REST API middlewares.
 *
 * A middleware can:
 * - Inspect or modify the incoming WP_REST_Request
 * - Perform validation, authentication, or logging
 * - Stop the pipeline by returning a response or WP_Error
 *
 * Each middleware must implement a handle method that receives:
 * - WP_REST_Request $request — the current request object
 * - callable $next — the next middleware or the final action handler
 *
 * The middleware should call `$next($request)` to continue
 * the pipeline, or return a response/WP_Error to stop it.
 *
 * Example:
 * ```php
 * class ExampleMiddleware implements MiddlewareInterface
 * {
 *      public function handle(WP_REST_Request $request, callable $next): mixed
 *      {
 *          // Example: reject requests from non-admins
 *          if (!current_user_can('manage_options')) {
 *              return new WP_Error('permission_denied', 'Access denied', ['status' => 403]);
 *          }
 *
 *          // Continue pipeline
 *          return $next($request);
 *      }
 * }
 * ```
 *
 * @package gi_api_route\Contracts
 * @author Artem <gammaironak@gmail.com>
 * @date 27.11.2025
 */

namespace gi_api_route\Contracts;
use WP_REST_Request;

interface MiddlewareInterface
{

    /**
     * Handles a REST API request in the middleware pipeline.
     *
     * @param WP_REST_Request $request The current REST API request
     * @param callable $next The next middleware or final action in the pipeline
     * @return mixed Return modified request, response, or WP_Error to stop the pipeline
     */
    public function handle(WP_REST_Request $request, callable $next): mixed;

}

