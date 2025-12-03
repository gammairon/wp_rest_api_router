<?php
/**
 * /**
 *  Represents a REST API namespace group.
 *
 *  A namespace group is a container for multiple routes under a common base URI.
 *  Provides middleware support via MiddlewareAwareInterface + HasMiddleware trait.
 *
 *  Example usage:
 *    $group = new NamespaceGroup('myslug/v1');
 *    $group->addRoute(new MyRoute('/endpoint'));
 *
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace gi_api_route\Routing;

use gi_api_route\Abstract\BaseRoute;
use gi_api_route\Contracts\MiddlewareAwareInterface;
use gi_api_route\Traits\HasMiddleware;

final class NamespaceGroup implements MiddlewareAwareInterface
{

    use HasMiddleware;

    /**
     * List of routes registered under this namespace.
     *
     * @var list<BaseRoute>
     */
    private array $routes = [];

    /**
     * Base URI for the namespace group.
     * Example: 'myslug/v1'
     *
     * @var string
     */
    public readonly string $baseUri;

    /**
     * Constructor.
     *
     * @param string $baseUri Base URI for this namespace group
     */
    public function __construct(string $baseUri)
    {
        $this->baseUri = trim($baseUri, '/');
    }

    /**
     * Adds a single route to this namespace group.
     *
     * @param BaseRoute $route
     * @return void
     */
    public function addRoute(BaseRoute $route): void
    {
        $this->routes[] = $route;
    }

    /**
     * Adds multiple routes to this namespace group.
     *
     * @param list<BaseRoute> $routes
     * @return void
     */
    public function addRoutes(array $routes): void
    {
        array_map([$this, 'addRoute'], $routes);
    }


    /**
     * Returns all routes registered under this namespace group.
     *
     * @return list<BaseRoute>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Creates a new route within this namespace group.
     *
     * This method instantiates a Route object for the given endpoint URI,
     * adds it to the current namespace group, and returns it for further configuration.
     *
     * @param string $endpointUri The URI path for the route, relative to the namespace base.
     * @return Route The newly created Route object for chaining actions and middleware.
     */
    public function route(string $endpointUri): Route
    {
        $route = new Route($endpointUri);

        $this->addRoute($route);

        return $route;
    }


    /**
     * Executes a scoped configuration block for this namespace group.
     *
     * The given callback receives the current NamespaceGroup instance,
     * allowing grouped/inline configuration in a fluent (chainable) style.
     *
     * Example:
     *    $group->scope(function(NamespaceGroup $g) {
     *        $g->addRoute(new MyRoute('/one'));
     *        $g->addRoute(new MyRoute('/two'));
     *    });
     *
     * @param callable(self): void $callback Callback receiving the current instance
     * @return $this
     */
    public function scope(callable $callback): self
    {
        $callback($this);
        return $this;
    }

}
