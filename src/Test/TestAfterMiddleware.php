<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Test;

use gi_api_route\Contracts\AfterMiddlewareInterface;
use gi_api_route\Support\Logger;
use WP_REST_Request;

class TestAfterMiddleware implements AfterMiddlewareInterface
{

    public function handle(WP_REST_Request $request, mixed $response, callable $next): mixed
    {
        Logger::write('I am test TestAfterMiddleware');

        return $next($request);
    }
}
