<?php
/**
 * Data Transfer Object used to pass configuration into BaseBuilder
 * (action, route, namespace group).
 *
 * This DTO ensures strict typing, improves readability,
 * and provides a single transport structure for builder dependencies.
 *
 * @author Artem
 * @date 26.11.2025
 */

namespace gi_api_route\DTO;

use gi_api_route\Abstract\BaseAction;
use gi_api_route\Abstract\BaseRoute;
use gi_api_route\Routing\NamespaceGroup;

/**
 * Class BuilderDTO
 *
 * Encapsulates the required elements for middleware and permisssion
 * builder initialization: the action, route, and namespace group.
 *
 * @package gi_api_route\DTO
 */
final class BuilderDTO
{
    /**
     * @param BaseAction      $action Action handler containing callback logic.
     * @param BaseRoute       $route  Route definition with URL pattern and actions.
     * @param NamespaceGroup  $group  Namespace group representing REST namespace.
     */
    public function __construct(
        public readonly BaseAction $action,
        public readonly BaseRoute $route,
        public readonly NamespaceGroup $group
    ) {}
}
