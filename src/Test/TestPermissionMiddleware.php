<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Test;

use gi_api_route\Contracts\PermissionMiddlewareInterface;
use gi_api_route\Support\Logger;
use WP_Error;
use WP_REST_Request;

class TestPermissionMiddleware implements PermissionMiddlewareInterface
{

    public function handle(WP_REST_Request $request, callable $next): mixed
    {
        //Logger::write('I am TestPermissionMiddleware');
        return $next($request);
    }
}
