<?php
/**
 * Interface for permission middleware in REST API pipeline.
 *
 * A permission middleware is responsible for:
 * - Authorizing access to a REST API endpoint
 * - Returning `true` if access is allowed
 * - Returning `false` or `WP_Error` to deny access
 *
 * This interface extends MiddlewareInterface, so it must implement
 * a handle method that receives:
 * - WP_REST_Request $request — the current request
 * - callable $next — the next permission middleware or default handler
 *
 * Typical usage:
 * ```php
 * class AdminOnlyMiddleware implements PermissionMiddlewareInterface
 * {
 *      public function handle(WP_REST_Request $request, callable $next): mixed
 *      {
 *          if (!current_user_can('manage_options')) {
 *              return new WP_Error('permission_denied', 'Access denied', ['status' => 403]);
 *          }
 *          return $next($request);
 *      }
 * }
 * ```
 *
 * @package gi_api_route\Contracts
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Contracts;


interface PermissionMiddlewareInterface extends MiddlewareInterface
{}
