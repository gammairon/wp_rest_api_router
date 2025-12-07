<?php
/**
 *
 * ThrottleMiddleware
 *
 * Enforces rate limiting for WordPress REST API requests.
 *
 * Authenticated users are tracked by user ID, guests by IP address.
 * Uses a fixed time window strategy: each window starts when the first request
 * is made and lasts for a defined duration.
 *
 * Returns a WP_Error with HTTP status 429 if the request limit is exceeded.
 *
 * Usage examples:
 * - Alias without parameters: 'throttle"
 * - Alias with parameters: "throttle:10;60"
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Before;

use GiApiRoute\Contracts\BeforeMiddlewareInterface;
use GiApiRoute\Support\Logger;
use WP_REST_Request;
use WP_Error;

class ThrottleMiddleware implements BeforeMiddlewareInterface
{
    /**
     * Maximum allowed requests within the time window.
     *
     * @var int
     */
    private int $limit;

    /**
     * Time window duration in seconds.
     *
     * @var int
     */
    private int $windowSeconds;



    /**
     * Constructor.
     *
     * @param int $limit Maximum number of requests allowed in the window.
     * @param int $windowSeconds Duration of the rate limit window in seconds.
     */
    public function __construct(int|string $limit = 10, int|string $windowSeconds = 60)
    {
        $this->limit = max(1, (int)$limit); // Ensure at least 1 request
        $this->windowSeconds = max(1, (int)$windowSeconds); // Ensure at least 1 second


    }


    /**
     * Handle the incoming request and enforce rate limiting.
     *
     * Uses a fixed time window strategy: once the window starts,
     * it remains fixed until expiration, preventing window sliding.
     *
     * @param WP_REST_Request $request Incoming REST request.
     * @param callable $next Next middleware or handler in the pipeline.
     *
     * @return mixed Returns false if rate limit exceeded, otherwise result of $next.
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        $key = $this->getThrottleKey();

        // Retrieve current throttle data
        $data = get_transient($key);

        // Initialize new window if no data exists
        if ($data === false) {
            $data = [
                'count' => 0,
                'expires_at' => time() + $this->windowSeconds
            ];
        }

        $currentTime = time();

        // Reset window if expired
        if ($currentTime >= $data['expires_at']) {
            $data = [
                'count' => 0,
                'expires_at' => $currentTime + $this->windowSeconds
            ];
        }

        // Check if limit exceeded
        if ($data['count'] >= $this->limit) {
            // Calculate remaining time in the window
            $retryAfter = max(0, $data['expires_at'] - $currentTime);

            return new WP_Error(
                'rest_rate_limit_exceeded',
                sprintf('Rate limit exceeded. Try again in %d seconds.', $retryAfter),
                [
                    'status' => 429,
                    'headers' => [
                        'Retry-After' => $retryAfter,
                        'X-RateLimit-Limit' => $this->limit,
                        'X-RateLimit-Remaining' => 0,
                        'X-RateLimit-Reset' => $data['expires_at']
                    ]
                ]
            );
        }

        // Increment request count
        $data['count']++;

        // Calculate exact TTL remaining in the window
        $ttl = max(1, $data['expires_at'] - $currentTime);

        // Save updated data
        set_transient($key, $data, $ttl);

        // Proceed to next middleware or handler
        return $next($request);
    }

    /**
     * Generate a unique throttle key based on user ID or IP address.
     *
     * Authenticated users are tracked by user ID, guests by IP address.
     *
     * @return string Unique throttle key.
     */
    private function getThrottleKey(): string
    {
        $user = wp_get_current_user();

        if ($user && $user->exists()) {
            return "rest_throttle_user_$user->ID";
        }

        // For guests, use IP address (sanitized)
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip = preg_replace('/[^0-9a-f.:]/i', '', $ip); // Sanitize IP

        return 'rest_throttle_ip_' . md5($ip);
    }
}
