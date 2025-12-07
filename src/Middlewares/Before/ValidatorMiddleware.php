<?php
/**
 * Request validation middleware for WordPress REST API.
 *
 * Uses Respect/Validation to validate request parameters before the route handler executes.
 * If validation fails, middleware returns a WP_Error with HTTP status 422.
 *
 * Example usage:
 * new ValidatorMiddleware([
 *     'email' => v::email(),
 *     'age' => v::intVal()->min(18)
 * ]);
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 06.12.2025
 */

namespace GiApiRoute\Middlewares\Before;

use GiApiRoute\Contracts\BeforeMiddlewareInterface;
use Respect\Validation\Exceptions\ValidationException;
use WP_REST_Request;
use WP_Error;

class ValidatorMiddleware implements BeforeMiddlewareInterface
{
    /**
     * Validation rules mapped by field name.
     *
     * Example:
     * [
     *   'email' => Validator::email(),
     *   'age' => Validator::intVal()->min(18)
     * ]
     *
     * @var array<string, \Respect\Validation\Validator>
     */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Handle incoming request and apply validation rules.
     *
     * - Extracts all request parameters
     * - Validates each parameter using Respect\Validation
     * - Collects validation errors
     * - Returns WP_Error(422) if validation fails
     *
     * @param WP_REST_Request $request The incoming REST API request.
     * @param callable $next The next middleware or the final route handler.
     *
     * @return mixed Either WP_Error (on validation error) or result of $next($request)
     */
    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        // Extract all input parameters
        $params = $request->get_params();

        // Storage for validation errors
        $errors = [];

        // Iterate through all defined validation rules
        foreach ($this->rules as $field => $validator) {
            // Get field value or null if missing
            $value = $params[$field] ?? null;

            try {
                // Validate field value
                $validator->assert($value);
            } catch (ValidationException $e) {
                // Collect validation errors for the field
                $errors[$field] = $e->getMessages();
            }
        }

        // If any validation error occurred — return 422 Unprocessable Entity
        if (!empty($errors)) {
            return new WP_Error(
                'rest_validation_failed',
                'Validation error',
                [
                    'status' => 422,
                    'errors' => $errors
                ]
            );
        }

        return $next($request);
    }
}
