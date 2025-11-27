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

namespace gi_api_route\Concrete;

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

}
