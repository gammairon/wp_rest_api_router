<?php
/**
 *  Abstract base class for defining REST API actions.
 *
 *  Handles:
 *   - HTTP method normalization and validation
 *   - Middleware assignment (via MiddlewareAwareInterface + HasMiddleware)
 *   - Execution of the action logic (handle method)
 *
 *  Each Action must extend this class and implement the `handle()` method.
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace gi_api_route\Abstract;

use gi_api_route\Contracts\MiddlewareAwareInterface;
use gi_api_route\Enums\HttpMethod;
use gi_api_route\Support\StringHelper;
use gi_api_route\Traits\HasMiddleware;
use WP_REST_Request;
use InvalidArgumentException;

abstract class BaseAction implements MiddlewareAwareInterface
{
    use HasMiddleware;

    /**
     * List of allowed HTTP methods for this action.
     *
     * Normalized to uppercase
     * (e.g. "get,post" → ["GET", "POST"])
     *
     * @var list<string>
     */
    public readonly array $httpMethods;

    /**
     * Whether the route should appear in WordPress REST API index output.
     *
     * @var bool
     */
    public bool $showInIndex = true;

    /**
     * @param string|array $httpMethods
     *        Accepted formats:
     *          - "GET"
     *          - "GET,POST"
     *          - ["GET", "POST"]
     */
    public function __construct( string|array $httpMethods)
    {
        $this->httpMethods = $this->prepareHttpMethods($httpMethods);
    }

    /**
     * Main action entrypoint.
     *
     * Implement this in concrete Actions to define business logic.
     *
     * @param WP_REST_Request $request
     * @return mixed Response or data structure returned to WP REST API.
     */
    abstract public function handle(WP_REST_Request $request): mixed;

    /**
     * Normalizes the HTTP methods input into a clean validated list.
     *
     * Steps:
     *  - Convert comma-separated string into an array.
     *  - Trim whitespace.
     *  - Uppercase all values.
     *  - Remove duplicates and empty values.
     *  - Validate each method against HttpMethod enum.
     *
     * @param string|array $methods
     * @return list<string>
     */
    private function prepareHttpMethods(string|array $methods): array
    {
        // Convert string like "GET,POST" to array
        if (is_string($methods)) {
            $methods = array_map('trim', explode(',', $methods));
        }
        // Convert to uppercase
        $methods = array_map([StringHelper::class, 'upper'], $methods);

        // Remove duplicates
        $methods = array_unique($methods);

        // Remove empty values
        $methods = array_filter($methods);

        // Validate against HttpMethod enum
        $this->validateMethods($methods);

        // Reset array keys
        return array_values($methods);

    }

    /**
     * Validates an array of HTTP methods.
     *
     * Ensures:
     *  - At least one method is provided.
     *  - Every method exists in HttpMethod enum.
     *
     * @param list<string> $methods
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function validateMethods(array $methods): void
    {
        if (count($methods) === 0) {
            throw new InvalidArgumentException('Action must contain at least one HTTP method.');
        }

        foreach ($methods as $methodString) {
            if (!HttpMethod::tryFrom($methodString)) {
                throw new InvalidArgumentException("Unsupported HTTP method $methodString");
            }
        }
    }

}
