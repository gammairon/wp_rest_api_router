<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace gi_api_route\Traits;

use gi_api_route\Contracts\AfterMiddlewareInterface;
use gi_api_route\Contracts\BeforeMiddlewareInterface;
use gi_api_route\Contracts\PermissionMiddlewareInterface;
use gi_api_route\Enums\MiddlewareType;
use gi_api_route\Support\MiddlewareManager;
use InvalidArgumentException;

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

    /**
     * Registers a list of middlewares for the current action or controller.
     *
     * The `$middlewareList` array should be structured as:
     * ```php
     * [
     *     'PERMISSION' => ['logger', AuthMW::class, new CustomMW()],
     *     'BEFORE'     => ['logger', AuthMW::class],
     *     'AFTER'      => ['tracker', CustomAfterMW::class],
     * ]
     * ```
     *
     * The keys correspond to middleware types and are case-insensitive.
     * Middleware types must match the `MiddlewareType` enum values.
     * The values are lists of middleware definitions, which can be:
     *  - Object instances of middleware
     *  - Class strings (e.g., `AuthMW::class`)
     *  - Aliases (optionally with parameters, e.g., `'logger:param1,param2'`)
     *
     * This method will resolve all middleware definitions using `MiddlewareManager`
     * and attach them to the appropriate pipeline (permission, before, after).
     *
     * @param array<string, array> $middlewareList List of middlewares grouped by type
     *
     * @return static Returns `$this` for fluent chaining
     *
     * @throws \InvalidArgumentException If an unknown middleware type is provided
     * @see MiddlewareType
     * @see MiddlewareManager
     */
    public function middlewares(array $middlewareList): static
    {
        $middlewareManager = new MiddlewareManager();

        foreach ($middlewareList as $middlewareType => $middlewares) {

            $typeEnum = MiddlewareType::tryFrom(strtoupper($middlewareType));

            if (!$typeEnum) {
                throw new InvalidArgumentException("Unknown middleware type: $middlewareType");
            }


            $methodName = match($typeEnum) {
                MiddlewareType::PERMISSION => 'addPermissionMiddleware',
                MiddlewareType::BEFORE     => 'addBeforeMiddleware',
                MiddlewareType::AFTER      => 'addAfterMiddleware',
            };


            $resolvedMiddlewares = $middlewareManager->resolveList($typeEnum, $middlewares);


            foreach ($resolvedMiddlewares as $mw) {
                $this->{$methodName}($mw);
            }
        }

        return $this;
    }

}
