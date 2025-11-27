<?php
/**
 * Interface for entities that can have middlewares attached.
 *
 * Any class implementing this interface can register and retrieve
 * permission, before, and after middlewares. This is typically used
 * for REST API routes, route groups, or actions in a pipeline.
 *
 * Middlewares:
 * - PermissionMiddlewareInterface: for access control
 * - BeforeMiddlewareInterface: for pre-processing requests
 * - AfterMiddlewareInterface: for post-processing responses
 *
 * @package gi_api_route\Contracts
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace gi_api_route\Contracts;

interface MiddlewareAwareInterface
{
    /**
     * Adds a permission middleware.
     *
     * @param PermissionMiddlewareInterface $middleware
     * @return static
     */
    public function addPermissionMiddleware(PermissionMiddlewareInterface $middleware): static;

    /**
     * Adds a before middleware.
     *
     * @param BeforeMiddlewareInterface $middleware
     * @return static
     */
    public function addBeforeMiddleware(BeforeMiddlewareInterface $middleware): static;

    /**
     * Adds an after middleware.
     *
     * @param AfterMiddlewareInterface $middleware
     * @return static
     */
    public function addAfterMiddleware(AfterMiddlewareInterface $middleware): static;

    /**
     * Returns all registered permission middlewares.
     *
     * @return list<PermissionMiddlewareInterface>
     */
    public function getPermissionMiddlewares(): array;

    /**
     * Returns all registered before middlewares.
     *
     * @return list<BeforeMiddlewareInterface>
     */
    public function getBeforeMiddlewares(): array;

    /**
     * Returns all registered after middlewares.
     *
     * @return list<AfterMiddlewareInterface>
     */
    public function getAfterMiddlewares(): array;
}
