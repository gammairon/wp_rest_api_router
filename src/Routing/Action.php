<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 02.12.2025
 */

namespace gi_api_route\Routing;

use gi_api_route\Abstract\BaseAction;
use WP_REST_Request;
use RuntimeException;

final class Action extends BaseAction
{
    /**
     * Fully qualified controller class name.
     *
     * @var string
     */
    private string $controllerClass;

    /**
     * Controller method to execute.
     *
     * @var string
     */
    private string $method;

    /**
     * @param string $controllerClass Class name of the controller
     * @param string $method Method inside the controller
     * @param string|array $httpMethods Allowed HTTP methods for this action
     */
    public function __construct(string $controllerClass, string $method, string|array $httpMethods)
    {
        parent::__construct($httpMethods);

        $this->controllerClass = $controllerClass;
        $this->method = $method;

        // Ensure controller exists and method is callable
        $this->validateController();
    }

    /**
     * Executes the controller method and returns its response.
     *
     * @param WP_REST_Request $request Incoming REST request
     * @return mixed Whatever the controller method returns
     */
    public function handle(WP_REST_Request $request): mixed
    {
        return (new $this->controllerClass())->{$this->method}($request);
    }

    /**
     * Validates that:
     *  - the controller class exists
     *  - the method exists and is callable
     *
     * @throws RuntimeException If controller or method is invalid
     */
    private function validateController(): void
    {
        if (!class_exists($this->controllerClass)) {
            throw new RuntimeException(
                "Controller class '$this->controllerClass' does not exist."
            );
        }

        if (!method_exists($this->controllerClass, $this->method)) {
            throw new RuntimeException(
                "Method '$this->method' does not exist in controller '$this->controllerClass'."
            );
        }

    }
}
