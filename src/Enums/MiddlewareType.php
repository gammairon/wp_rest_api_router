<?php
/**
 * Enum representing types of middleware in the API pipeline.
 *
 * Used to categorize middlewares according to their execution timing:
 * - BEFORE: executed before the action
 * - AFTER: executed after the action
 * - PERMISSION: executed to check permissions before the action
 *
 * @package gi_api_route\Enums
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Enums;

enum MiddlewareType
{
    /** Middleware executed before the main action is called. */
    case BEFORE;

    /** Middleware executed after the main action is called, often for response modification. */
    case AFTER;

    /** Middleware executed to check permissions or access rights before the action. */
    case PERMISSION;
}
