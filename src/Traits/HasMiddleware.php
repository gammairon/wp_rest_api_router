<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace gi_api_route\Traits;

use gi_api_route\Contracts\AfterMiddlewareInterface;
use gi_api_route\Contracts\BeforeMiddlewareInterface;
use gi_api_route\Contracts\PermissionMiddlewareInterface;

trait HasMiddleware
{

    /**
     * List of middleware responsible for permission checks.
     *
     * @var list<PermissionMiddlewareInterface>
     */
    private array $permissionMiddlewares = [];

    /**
     * List of middleware executed before the main logic.
     *
     * @var list<BeforeMiddlewareInterface>
     */
    private array $beforeMiddlewares = [];

    /**
     * List of middleware executed after the main logic.
     *
     * @var list<AfterMiddlewareInterface>
     */
    private array $afterMiddlewares = [];

    /**
     * Adds a permission middleware.
     *
     * @param PermissionMiddlewareInterface $middleware
     * @return static
     */
    public function addPermissionMiddleware(PermissionMiddlewareInterface $middleware): static
    {
        $this->permissionMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds a middleware to be executed before the main logic.
     *
     * @param BeforeMiddlewareInterface $middleware
     * @return static
     */
    public function addBeforeMiddleware(BeforeMiddlewareInterface $middleware): static
    {
        $this->beforeMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds a middleware to be executed after the main logic.
     *
     * @param AfterMiddlewareInterface $middleware
     * @return static
     */
    public function addAfterMiddleware(AfterMiddlewareInterface $middleware): static
    {
        $this->afterMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Returns the list of permission middleware.
     *
     * @return list<PermissionMiddlewareInterface>
     */
    public function getPermissionMiddlewares(): array
    {
        return $this->permissionMiddlewares;
    }

    /**
     * Returns the list of middleware executed before the main logic.
     *
     * @return list<BeforeMiddlewareInterface>
     */
    public function getBeforeMiddlewares(): array
    {
        return $this->beforeMiddlewares;
    }

    /**
     * Returns the list of middleware executed after the main logic.
     *
     * @return list<AfterMiddlewareInterface>
     */
    public function getAfterMiddlewares(): array
    {
        return $this->afterMiddlewares;
    }

}
