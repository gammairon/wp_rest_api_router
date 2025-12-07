<?php
/**
 * Authentication middleware for WordPress REST API.
 *
 * Ensures the request is made by an authenticated user.
 * If the user is not logged in, the middleware blocks the request.
 *
 * Alias: auth
 *
 * @author Artem <gammaironak@gmail.com
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Permission;

use GiApiRoute\Contracts\PermissionMiddlewareInterface;
use WP_REST_Request;

class AuthMiddleware implements PermissionMiddlewareInterface
{
    /**
     * Handle the incoming request and verify authentication status.
     *
     * @param WP_REST_Request $request The incoming REST request.
     * @param callable        $next    The next middleware or handler.
     *
     * @return mixed Returns false if the user is not authenticated.
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {

        // Check if user is authenticated
        if (!is_user_logged_in()) {
            return false; // Block unauthenticated requests
        }

        // User is authenticated, continue pipeline
        return $next($request);
    }
}
