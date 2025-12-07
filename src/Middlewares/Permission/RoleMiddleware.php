<?php
/**
 * Role check middleware for WordPress REST API.
 *
 * This middleware ensures that the authenticated user has at least one of the required roles.
 * If the user is not logged in or does not have any of the specified roles,
 * the request is denied (returns false) before reaching the actual handler.
 *
 * Usage examples:
 * - Alias without parameters: "role"
 * - Alias with parameters: "role:editor,administrator"
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Permission;

use GiApiRoute\Contracts\PermissionMiddlewareInterface;
use WP_REST_Request;

class RoleMiddleware implements PermissionMiddlewareInterface
{
    /**
     * @var array List of required user roles.
     */
    private array $roles;

    /**
     * Constructor.
     *
     * Accepts a single role string, an array of roles, or null.
     *
     * @param string|array|null $roles Role or list of roles to check.
     */
    public function __construct(array|string|null $roles = null)
    {
        // Normalize input to array
        $this->roles = is_string($roles) ? [$roles] : ($roles ?? []);
    }

    /**
     * Handle the incoming REST request.
     *
     * Verifies that the current user exists and has at least one of the specified roles.
     * If verification fails, returns false (403 Forbidden). Otherwise, continues to the next middleware or handler.
     *
     * @param WP_REST_Request $request Incoming request object.
     * @param callable $next Callback for the next middleware or handler.
     *
     * @return mixed Returns false if role check fails; otherwise returns the result of $next.
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        $user = wp_get_current_user();

        // User must be logged in
        if (!$user || !$user->exists()) {
            return false; // 403 Forbidden
        }

        // Check roles if specified
        if (!empty($this->roles)) {
            $hasRole = array_intersect($user->roles, $this->roles);
            if (empty($hasRole)) {
                return false; // User role not allowed
            }
        }

        // All checks passed, proceed
        return $next($request);
    }
}
