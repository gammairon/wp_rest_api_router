<?php
/**
 * Enum representing standard HTTP methods.
 *
 * This enum is used to validate and type-hint HTTP methods
 * in REST API actions and routes.
 *
 * Example usage:
 * HttpMethod::GET
 * HttpMethod::POST
 *
 * @package GiApiRoute\Enums
 * @author Artem <gammaironak@gmail.com>
 * @date 20.11.2025
 */

namespace GiApiRoute\Enums;

enum HttpMethod: string
{
    /** The GET method requests a representation of the specified resource. */
    case GET = 'GET';

    /** The HEAD method asks for a response identical to a GET request, but without the response body. */
    case HEAD = 'HEAD';

    /** The POST method submits data to be processed to a specified resource. */
    case POST = 'POST';

    /** The PUT method replaces all current representations of the target resource with the request payload. */
    case PUT = 'PUT';

    /** The PATCH method applies partial modifications to a resource. */
    case PATCH = 'PATCH';

    /** The DELETE method deletes the specified resource. */
    case DELETE = 'DELETE';

    /** The OPTIONS method describes the communication options for the target resource. */
    case OPTIONS = 'OPTIONS';
}
