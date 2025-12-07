<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Before;

use GiApiRoute\Contracts\BeforeMiddlewareInterface;
use WP_REST_Request;
use WP_Error;

class GeoMiddleware implements BeforeMiddlewareInterface
{
    private array $countries;
    private string $mode; // 'allow' or 'block'
    private ?string $cache = null;

    /**
     * Constructor.
     *
     * @param array $countries List of country codes (ISO 3166-1 alpha-2).
     * @param string $mode 'allow' (whitelist) or 'block' (blacklist).
     */
    public function __construct(array $countries, string $mode = 'allow')
    {
        $this->countries = array_map('strtoupper', $countries);
        $this->mode = strtolower($mode) === 'block' ? 'block' : 'allow';
    }

    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        $country = $this->getCountryCode();

        if ($country === null) {
            // Failed to detect country - allow by default
            return $next($request);
        }

        $isInList = in_array($country, $this->countries, true);

        // Allow mode: only listed countries pass
        if ($this->mode === 'allow' && !$isInList) {
            return new WP_Error(
                'rest_geo_restricted',
                sprintf('Access denied from your location: %s.', $country ?: 'unknown'),
                ['status' => 403]
            );
        }

        // Block mode: listed countries are blocked
        if ($this->mode === 'block' && $isInList) {
            return new WP_Error(
                'rest_geo_restricted',
                sprintf('Access denied from your location: %s.', $country ?: 'unknown'),
                ['status' => 403]
            );
        }

        return $next($request);
    }

    /**
     * Get country code from IP address.
     *
     * @return string|null Two-letter country code or null if detection fails.
     */
    private function getCountryCode(): ?string
    {
        // Check cache to avoid multiple API calls
        if ($this->cache !== null) {
            return $this->cache;
        }

        // CloudFlare (fastest if available)
        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            return $this->cache = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // API lookup
        return $this->lookupCountry($ip);

    }

    /**
     * Lookup country via external API.
     *
     * @param string $ip IP address to lookup.
     * @return string|null Country code or null.
     */
    private function lookupCountry(string $ip): ?string
    {
        // Use ip-api.com (free, no key required)
        $response = wp_remote_get(
            "http://ip-api.com/json/{$ip}?fields=countryCode",
            ['timeout' => 2]
        );

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data['countryCode'] ?? null;
    }
}
