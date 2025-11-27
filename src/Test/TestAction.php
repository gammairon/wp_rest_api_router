<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace gi_api_route\Test;

use gi_api_route\Abstract\BaseAction;
use gi_api_route\Support\Logger;
use WP_REST_Request;

class TestAction extends BaseAction
{

    public function handle(WP_REST_Request $request): mixed
    {
        Logger::write('I am TestAction with methods: ' . implode(', ', $this->httpMethods) );

        return [
            'say' => 'Hello World!'
        ];
    }
}
