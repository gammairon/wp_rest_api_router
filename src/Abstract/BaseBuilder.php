<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Abstract;

use gi_api_route\Concrete\NamespaceGroup;
use gi_api_route\Contracts\MiddlewareAwareInterface;
use gi_api_route\DTO\BuilderDTO;
use gi_api_route\Enums\MiddlewareType;
use WP_REST_Request;

/**
 * BaseBuilder provides a foundation for building middleware pipelines
 * for REST API routes. It orchestrates middlewares from the namespace
 * group, route, and action levels and allows executing them in the correct order.
 *
 * Usage example:
 * ```php
 * $builderDTO = new BuilderDTO($action, $route, $group);
 * $builder = new SomeConcreteBuilder($builderDTO);
 * $response = $builder($request); // calls __invoke()
 * ```
 */
abstract class BaseBuilder
{
    /**
     * The action associated with this builder.
     *
     * @var BaseAction
     */
    protected BaseAction $action;

    /**
     * The route associated with this builder.
     *
     * @var BaseRoute
     */
    protected BaseRoute $route;

    /**
     * The namespace group associated with this builder.
     *
     * @var NamespaceGroup
     */
    protected NamespaceGroup $group;

    /**
     * BaseBuilder constructor.
     *
     * @param BuilderDTO $builderDTO DTO containing action, route, and group
     */
    public function __construct(BuilderDTO $builderDTO)
    {
        $this->action = $builderDTO->action;
        $this->route = $builderDTO->route;
        $this->group = $builderDTO->group;
    }

    /**
     * Invokes the middleware pipeline for the current action.
     *
     * This allows the builder to be used as a callable directly.
     *
     * @param WP_REST_Request $request The REST API request object
     * @return mixed The response after passing through the middleware pipeline
     */
    public function __invoke(WP_REST_Request $request): mixed
    {
        $pipelineFunc = $this->build();

        return $pipelineFunc($request);
    }

    /**
     * Build the complete middleware pipeline.
     *
     * Concrete builders must implement this method and return a callable
     * that processes the request through all middlewares and finally
     * executes the action.
     *
     * @return callable A callable that receives WP_REST_Request and returns the response
     */
    abstract public function build(): callable;

    /**
     * Extract middlewares of a specific type from a middleware-aware entity.
     *
     * @param MiddlewareAwareInterface $aware The entity (NamespaceGroup, BaseRoute, or BaseAction)
     * @param MiddlewareType $type Type of middleware (BEFORE, AFTER, PERMISSION)
     * @return array List of middleware instances (may be empty)
     */
    private function extractByType(MiddlewareAwareInterface $aware, MiddlewareType $type): array
    {
        $middlewares =  match ($type) {
            MiddlewareType::BEFORE     => $aware->getBeforeMiddlewares(),
            MiddlewareType::AFTER      => $aware->getAfterMiddlewares(),
            MiddlewareType::PERMISSION => $aware->getPermissionMiddlewares(),
        };

        return $middlewares ?? [];
    }


    /**
     * Collects middlewares of the given type from the hierarchy:
     * group → route → action.
     *
     * This ensures that all middlewares for a request are executed in order:
     * 1. NamespaceGroup middlewares
     * 2. Route middlewares
     * 3. Action middlewares
     *
     * @param MiddlewareType $type Type of middleware to collect
     * @return array List of all middlewares of the given type
     */
    protected function getMiddlewares(MiddlewareType $type): array
    {
        return array_merge(
            $this->extractByType($this->group,  $type),
            $this->extractByType($this->route,  $type),
            $this->extractByType($this->action, $type),
        );
    }

}
