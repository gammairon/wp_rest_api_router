<?php
/**
 * Router class responsible for registering REST API namespace groups and routes.
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 19.11.2025
 */

namespace gi_api_route\Routing;

use gi_api_route\Builders\MiddlewareBuilder;
use gi_api_route\Builders\PermissionBuilder;
use gi_api_route\DTO\BuilderDTO;

final class Router
{

    /**
     * List of registered namespace groups.
     *
     * @var list<NamespaceGroup>
     */
    private array $namespaceGroups = [];

    /**
     * Adds a single NamespaceGroup to the router.
     *
     * @param NamespaceGroup $group
     * @return $this
     */
    public function addNamespaceGroup(NamespaceGroup $group): self
    {
        $this->namespaceGroups[] = $group;

        return $this;
    }


    /**
     * Adds multiple NamespaceGroup objects to the router.
     *
     * @param list<NamespaceGroup> $groups
     * @return $this
     */
    public function addNamespaceGroups(array $groups ): self
    {
        array_map([$this, 'addNamespaceGroup'], $groups);

        return $this;
    }

    /**
     * Registers a single NamespaceGroup and all its contained routes.
     *
     * @param NamespaceGroup $group
     * @return void
     */
    public function registerNamespaceGroup(NamespaceGroup $group): void
    {
        foreach ($group->getRoutes() as $route) {


            $actionGroup = array_map(static function($action) use ($route, $group) {

                $builderConfig = new BuilderDTO($action, $route, $group);

                return [
                    'methods'             => $action->httpMethods,
                    'callback'            => new MiddlewareBuilder($builderConfig),
                    'permission_callback' => new PermissionBuilder($builderConfig),
                    'show_in_index'       => $action->showInIndex
                ];

            }, $route->getActions());


            register_rest_route(
                $group->baseUri,
                $route->endpointUri,
                $actionGroup,
                $route->override
            );
        }

    }

    /**
     * Registers all NamespaceGroup objects stored in the router.
     *
     * @return void
     */
    public function registerNamespaceGroups(): void
    {
        array_map([$this, 'registerNamespaceGroup'], $this->namespaceGroups);
    }

    /**
     * Hooks router into WordPress lifecycle.
     *
     * Registers all namespace groups at rest_api_init.
     *
     * @return void
     */
    public function register(): void
    {
        add_action( 'rest_api_init', [ $this, 'registerNamespaceGroups' ] );
    }

}
