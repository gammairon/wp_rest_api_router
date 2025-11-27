<?php
/**
 * Abstract base class representing a REST API route.
 *
 * Handles:
 * - Managing a collection of BaseAction instances
 * - Validating action HTTP methods to avoid conflicts
 * - Optional overwrite behavior
 * - Middleware awareness via MiddlewareAwareInterface + HasMiddleware
 *
 * Example usage:
 * $route = new MyRoute('/endpoint-slug');
 * $route->addAction(new GetAction('GET'));
 *
 *
 * @author Artem <gammaironak@gmail.com>
 * @date 19.11.2025
 */

namespace gi_api_route\Abstract;

use gi_api_route\Contracts\MiddlewareAwareInterface;
use gi_api_route\Traits\HasMiddleware;
use InvalidArgumentException;

abstract class BaseRoute implements MiddlewareAwareInterface
{
    use HasMiddleware;

    /**
     * List of actions registered for this route.
     *
     * @var list<BaseAction>
     */
    protected array $actions = [];

    /**
     * Determines whether to overwrite the data if this route already exists:
     * true  - overwrite
     * false - merge using array_merge().
     *
     * @var bool
     */
    public bool $override = false;

    /**
     * Endpoint URI pattern.
     * Example: /endpoint-slug or /endpoint-slug/(?P<param_name>.+)
     *
     * @param string $endpointUri
     */
    public function __construct(public readonly string $endpointUri)
    {

    }


    /**
     * Adds a single action to this route.
     *
     * @param BaseAction $action
     * @return $this
     */
    public function addAction(BaseAction $action): self
    {

        $this->validateAction($action);

        $this->actions[] = $action;

        return $this;
    }

    /**
     * Validates whether the given action can be added to this route.
     *
     * Ensures that none of the HTTP methods in the validating action
     * are already used by existing actions in this route.
     *
     * @param BaseAction $validatingAction The action to validate
     * @return void
     * @throws InvalidArgumentException if one or more HTTP methods are already in use
     */
    public function validateAction(BaseAction $validatingAction): void
    {
        foreach ($this->actions as $action) {

            // Find overlapping HTTP methods
            $common = array_intersect(
                $action->httpMethods,
                $validatingAction->httpMethods
            );

            if (!empty($common)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Action for HTTP method(s) "%s" already exists in this route.',
                        implode('", "', $common)
                    )
                );
            }
        }

    }

    /**
     * Adds multiple actions to this route.
     *
     * @param list<BaseAction> $actions
     * @return $this
     */
    public function addActions(array $actions): self
    {
        array_map([$this, 'addAction'], $actions);

        return $this;
    }


    /**
     * Returns all actions registered for this route.
     *
     * @return list<BaseAction>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

}
