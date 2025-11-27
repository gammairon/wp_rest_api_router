<?php
/**
 * Interface for "before" middlewares in REST API pipeline.
 *
 * Before middlewares are executed **before** the main action handler.
 * They can:
 * - modify the incoming WP_REST_Request,
 * - perform validation,
 * - block the request by returning a WP_Error or any other response.
 *
 * Each middleware receives:
 * - WP_REST_Request $request — the current REST API request
 * - callable $next — the next middleware in the pipeline or the action itself
 *
 * The middleware should call `$next($request)` to continue
 * the pipeline, or return a response/WP_Error to stop it.
 *
 * Example:
 * ```php
 * class CheckAuthMiddleware implements BeforeMiddlewareInterface
 * {
 *      public function handle(WP_REST_Request $request, callable $next): mixed
 *      {
 *          if (!current_user_can('edit_posts')) {
 *              return new WP_Error('permission_denied', 'User cannot edit posts', ['status' => 403]);
 *          }
 *          return $next($request);
 *      }
 * }
 * ```
 * ```
 *
 * @package gi_api_route\Contracts
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Contracts;

interface BeforeMiddlewareInterface extends MiddlewareInterface
{}
