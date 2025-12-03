<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 02.12.2025
 */

namespace gi_api_route\Routing;

use gi_api_route\Abstract\BaseAction;
use gi_api_route\Abstract\BaseRoute;
use gi_api_route\Enums\HttpMethod;
use WP_REST_Request;

final class Route extends BaseRoute
{
    /**
     * Registers GET endpoint.
     */
    public function get(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::GET->value]);
    }

    /**
     * Registers HEAD endpoint.
     */
    public function head(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::HEAD->value]);
    }

    /**
     * Registers POST endpoint.
     */
    public function post(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::POST->value]);
    }

    /**
     * Registers PUT endpoint.
     */
    public function put(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::PUT->value]);
    }

    /**
     * Registers PATCH endpoint.
     */
    public function patch(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::PATCH->value]);
    }

    /**
     * Registers DELETE endpoint.
     */
    public function delete(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::DELETE->value]);
    }

    /**
     * Registers OPTIONS endpoint.
     */
    public function options(string $controllerClass, string $method): Action
    {
        return $this->makeAction($controllerClass, $method, [HttpMethod::OPTIONS->value]);
    }

    /**
     * Registers multiple HTTP methods to a single controller method.
     *
     * @param string $controllerClass
     * @param string $method
     * @param string[]|string $httpMethods
     */
    public function group(string $controllerClass, string $method, array|string $httpMethods): Action
    {
        return $this->makeAction($controllerClass, $method, $httpMethods);
    }

    /**
     * Registers an inline callback as a route action.
     *
     * This method allows defining an action without creating a separate controller class.
     * It creates an anonymous class extending BaseAction and delegates the request
     * handling to the provided callback.
     *
     * Usage example:
     * ```
     * Route::namespaceGroup('v1')
     *     ->route('/ping')
     *     ->callback('GET', function (WP_REST_Request $request) {
     *         return ['pong' => true];
     *     });
     * ```
     *
     * @param array|string $httpMethods  One or more HTTP methods (e.g. 'GET', ['POST', 'PUT']).
     * @param callable $callback         Callback executed when the route is triggered.
     *                                   Signature: function(WP_REST_Request $request): mixed
     *
     * @return BaseAction Returns the created action instance for chaining (adding middleware, etc).
     */
    public function callback(array|string $httpMethods, callable $callback): BaseAction
    {
        $action = new class($httpMethods, $callback) extends BaseAction {

            /** @var callable */
            private $callback;

            /**
             * @param array|string $httpMethods
             * @param callable $callback
             */
            public function __construct(array|string $httpMethods, callable $callback)
            {
                parent::__construct($httpMethods);
                $this->callback = $callback;
            }

            /**
             * Executes the provided callback instead of a controller method.
             *
             * @param WP_REST_Request $request
             * @return mixed
             */
            public function handle(WP_REST_Request $request): mixed
            {
                return call_user_func($this->callback, $request);
            }
        };

        $this->addAction($action);

        return $action;
    }

    /**
     * Factory method that creates Action instance and attaches it to the route.
     *
     * @param string $controllerClass
     * @param string $method
     * @param string[]|string $httpMethods
     *
     * @return Action
     */
    private function makeAction(string $controllerClass, string $method, array|string $httpMethods): Action
    {
        $action = new Action($controllerClass, $method, $httpMethods);

        $this->addAction($action);

        return $action;
    }




}
