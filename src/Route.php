<?php
/**
 *
 * Facade for defining and registering API routes.
 *
 * This class provides a clean static interface for building route groups
 * and registering them in the internal router. It acts as an entry point
 * for users who define routes in a declarative style:
 *
 * Route::namespaceGroup('v1/users')
 * ->route('/profile')
 * ->get(UserController::class, 'profile')
 *
 * After all routes are declared, Route::registerRoutes() must be called
 * to register them in WordPress via WP_REST_API.
 *
 * @package GiApiRoute
 * @author Artem <gammaironak@gmail.com>
 * @date 02.12.2025
 */

namespace GiApiRoute;

use GiApiRoute\Routing\NamespaceGroup;
use GiApiRoute\Routing\Router;

final class Route
{
    /**
     * Single instance of the internal router.
     *
     * Router stores all namespace groups and routes, and
     * is responsible for registering them in WordPress.
     *
     * @var Router|null
     */
    private static ?Router $router = null;

    /**
     * Returns the shared router instance.
     * Lazily initializes the router on the first call.
     *
     * @return Router
     */
    private static function router(): Router
    {
        return self::$router ??= new Router();
    }

    /**
     * Creates and registers a new namespace group.
     *
     * Namespace groups logically separate route groups,
     * for example:
     *   Route::namespaceGroup('v1/users')
     *
     * This method does NOT register routes immediately;
     * it only attaches them to the internal router.
     *
     * @param string $baseUri Base REST namespace prefix (e.g. "v1/orders").
     * @return NamespaceGroup
     */
    public static function namespaceGroup(string $baseUri): NamespaceGroup
    {
        $group = new NamespaceGroup($baseUri);
        self::router()->addNamespaceGroup($group);
        return $group;
    }

    /**
     * Registers all routes in WordPress REST API.
     *
     * This method must be called once during plugin initialization,
     * usually inside a WordPress hook:
     *
     * add_action('rest_api_init', function () {
     *     Router::registerNamespaceGroups();
     * });
     *
     * It delegates the actual registration logic to the Router class.
     *
     * @return void
     */
    public static function registerRoutes(): void
    {
        self::router()->register();
    }
}
