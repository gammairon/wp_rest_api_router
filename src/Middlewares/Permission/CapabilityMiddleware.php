<?php
/**
 *
 *
 * Capability check middleware for WordPress REST API.
 *
 * This middleware ensures that the authenticated user has the required capability(s).
 * If the user is not logged in or does not possess the required capability,
 * the request will be denied (returns false) before reaching the actual handler.
 *
 * Usage examples:
 * - Alias without parameters: "capability"
 * - Alias with parameters: "capability:edit_posts,delete_posts"
 *
 * Default capability can be empty or a sensible default like 'manage_options'.
 *
 * @author capability <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Permission;

use GiApiRoute\Contracts\PermissionMiddlewareInterface;
use WP_REST_Request;

class CapabilityMiddleware implements PermissionMiddlewareInterface
{
    /**
     * @var array List of required capability names.
     */
    private array $capabilities;

    /**
     * Constructor.
     *
     * Accepts a single capability string, an array of capabilities, or null.
     *
     * @param string|array|null $capabilities Capability or list of capabilities to check.
     */
    public function __construct(array|string|null $capabilities = null)
    {
        // Normalize input to array
        $this->capabilities = is_string($capabilities) ? [$capabilities] : ($capabilities ?? []);
    }

    /**
     * Handle the incoming REST request.
     *
     * Verifies that the current user exists and has all specified capabilities.
     * If verification fails, returns false (403 Forbidden). Otherwise, continues to the next middleware or handler.
     *
     * @param WP_REST_Request $request Incoming request object.
     * @param callable $next Callback for the next middleware or handler.
     *
     * @return mixed Returns false if capability check fails; otherwise returns the result of $next.
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        $user = wp_get_current_user();

        // User must be logged in
        if (!$user || !$user->exists()) {
            return false; // 403 Forbidden
        }

        // Check capabilities if specified
        foreach ($this->capabilities as $cap) {
            if (!$user->has_cap($cap)) {
                return false; // Missing required capability
            }
        }

        // All checks passed, proceed to the next middleware or action
        return $next($request);
    }
}
