<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Builders;

use gi_api_route\Abstract\BaseBuilder;
use gi_api_route\Contracts\AfterMiddlewareInterface;
use gi_api_route\Contracts\BeforeMiddlewareInterface;
use gi_api_route\Enums\MiddlewareType;
use WP_REST_Request;
use Exception;
use WP_Error;

/**
 * Class MiddlewareBuilder
 *
 * Builds a complete middleware pipeline for WordPress REST API routes:
 * - BEFORE middlewares
 * - action handler
 * - AFTER middlewares
 */
final class MiddlewareBuilder extends BaseBuilder
{

    /**
     * Builds full processing pipeline:
     * BEFORE middlewares -> action -> AFTER middlewares.
     *
     * @return callable Middleware handler function
     */
    public function build(): callable
    {
        // Collect all BEFORE middlewares from group, route and action
        $beforeMiddlewares = $this->getMiddlewares(MiddlewareType::BEFORE);

        // Collect all AFTER middlewares from group, route and action
        $afterMiddlewares = $this->getMiddlewares(MiddlewareType::AFTER);


        /**
         * Final handler: executes the action, then runs AFTER middlewares
         */
        $finalHandler = function(WP_REST_Request $request) use ($afterMiddlewares) {
            try {
                // Execute action and get response
                $response = $this->action->handle($request);

                // Process AFTER middlewares
                return $this->executeAfterMiddlewares($afterMiddlewares, $request, $response);
            } catch (Exception $e) {
                // Handle exceptions thrown inside action
                return new WP_Error(
                    $e->getCode() ?: 'action_error',
                    $e->getMessage(),
                    ['status' => 500]
                );
            }
        };

        // Wrap action handler into BEFORE middleware pipeline
        return $this->pipelineBeforeMiddlewares($beforeMiddlewares, $finalHandler);
    }


    /**
     * Builds pipeline for BEFORE middlewares.
     *
     * Middleware chain structure:
     *   m1 -> m2 -> ... -> finalHandler
     *
     * @param list<BeforeMiddlewareInterface> $beforeMiddlewares
     * @param callable $finalHandler
     * @return callable
     */
    private function pipelineBeforeMiddlewares(array $beforeMiddlewares, callable $finalHandler): callable
    {
        // No BEFORE middlewares — return action handler directly
        if (empty($beforeMiddlewares)) {
            return $finalHandler;
        }

        /**
         * Build pipeline using array_reduce
         */
        return array_reduce(
            array_reverse($beforeMiddlewares),
            static function(callable $next, BeforeMiddlewareInterface $middleware) {
                return static function(WP_REST_Request $request) use ($next, $middleware) {
                    try {
                        /**
                         * BEFORE middleware can:
                         * - modify request and pass it forward by returning WP_REST_Request
                         * - interrupt execution by returning a non-request value (e.g. WP_Error)
                         */
                        return $middleware->handle($request, $next);


                    } catch (Exception $e) {
                        // Handle exceptions thrown inside BEFORE middleware
                        return new WP_Error(
                            $e->getCode() ?: 'before_middleware_error',
                            $e->getMessage(),
                            ['status' => 500]
                        );
                    }
                };
            },
            $finalHandler
        );

    }


    /**
     * Executes AFTER middlewares sequentially.
     *
     * Pipeline looks like:
     *   response -> m1 -> m2 -> ... -> final return
     *
     * @param list<AfterMiddlewareInterface> $afterMiddlewares
     * @param WP_REST_Request $request
     * @param mixed $response
     * @return mixed
     */
    private function executeAfterMiddlewares(array $afterMiddlewares, WP_REST_Request $request, mixed $response): mixed
    {
        // No AFTER middlewares — return action response as is
        if (empty($afterMiddlewares)) {
            return $response;
        }

        /**
         * Build AFTER middleware pipeline
         */
        $pipeline = array_reduce(
            array_reverse($afterMiddlewares),
            static function(callable $next, AfterMiddlewareInterface $middleware) use ($response) {
                return static function(WP_REST_Request $request) use ($response, $next, $middleware) {

                    try {

                        return $middleware->handle($request, $response, $next);

                    } catch (Exception $e) {
                        // Handle exceptions inside AFTER middleware
                        return new WP_Error(
                            $e->getCode() ?: 'after_middleware_error',
                            $e->getMessage(),
                            ['status' => 500]
                        );
                    }

                };
            },
            // Final step — return response unchanged
            static fn(WP_REST_Request $request) => $response
        );

        return $pipeline($request);
    }
}
