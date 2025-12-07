<?php
/**
 * Permission middleware for validating WordPress REST API nonces.
 *
 * This middleware checks the `X-WP-Nonce` header and verifies it
 * using the provided action name. If the nonce is invalid, the request
 * is rejected before reaching the next handler in the pipeline.
 *
 * Alias: nonce or nonce:action_name
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Permission;

use GiApiRoute\Contracts\PermissionMiddlewareInterface;
use WP_REST_Request;

class NonceMiddleware implements PermissionMiddlewareInterface
{
    /**
     * @var string The action name used when verifying the nonce.
     */
    private string $actionName;

    /**
     * Constructor.
     *
     * @param string|null $actionName Optional action name for nonce verification.
     *                                Defaults to "wp_rest" if not provided.
     */
    public function __construct(?string $actionName = null)
    {
        $this->actionName = $actionName ?? 'wp_rest';
    }

    /**
     * Handle the incoming request and verify the WordPress REST nonce.
     *
     * If the nonce is missing or invalid, the middleware returns false
     * (you may replace this with a WP_Error or custom response if needed).
     * Otherwise, execution is passed to the next middleware/handler.
     *
     * @param WP_REST_Request $request The incoming REST API request.
     * @param callable        $next    The next middleware or request handler.
     *
     * @return mixed Returns false on failure, otherwise the handler result.
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        // Extract the nonce from the request header.
        $nonce = $request->get_header('X-WP-Nonce') ?: '';

        // Verify the nonce based on the configured action name.
        if (!wp_verify_nonce($nonce, $this->actionName)) {
            return false; // Nonce invalid → request should be rejected.
        }

        // Nonce is valid → continue with the pipeline.
        return $next($request);
    }
}
