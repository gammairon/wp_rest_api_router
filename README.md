# GiApiRoute - WordPress REST API Route Module

### Overview

GiApiRoute is a powerful module designed to simplify and enhance the native WordPress REST API. It provides developers with an intuitive interface similar to popular frameworks like Laravel, making API development for WordPress more efficient and enjoyable.

### Installation

1. Copy the entire module directory to your desired location in WordPress:
    - For plugin-based APIs: copy to your plugin folder
    - For theme-based APIs: copy to your theme folder

2. Include the autoloader file in your plugin or theme:

```php
require_once 'path_to_module/module_directory/autoloader.php';
```

3. **⚠️ IMPORTANT: After registering all routes you MUST call:**

```php
\GiApiRoute\Route::registerRoutes();
```

> **This step is mandatory!** Without calling `registerRoutes()` your routes will not be registered and the API will not work correctly.

### Quick Start

The simplest way to register a route:

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint')
    ->get(YourController::class, 'controllerMethodName');
```

**API URL:** `http://example.com/wp-json/yourslug/v1/your-endpoint`

### Dynamic Routes

You can also create dynamic URLs with parameters:

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint/(?P<param_name>.+)')
    ->get(YourController::class, 'controllerMethodName');
```

**API URL:** `http://example.com/wp-json/yourslug/v1/your-endpoint/{param_name}`

### Namespace Groups

#### Using Scope Callback

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->scope(function ($namespace) {
        $namespace->route('/your-get-endpoint')
            ->get(YourController::class, 'get');
        $namespace->route('/your-post-endpoint')
            ->post(YourController::class, 'post');
    });
```

#### Using Direct Assignment

```php
$namespace = \GiApiRoute\Route::namespaceGroup('yourslug/v1');
$namespace->route('/your-get-endpoint')
    ->get(YourController::class, 'methodNameForGet');
$namespace->route('/your-post-endpoint')
    ->post(YourController::class, 'methodNameForPost');
```

### Route Methods

Routes support the following HTTP methods:
- `get()`
- `head()`
- `post()`
- `put()`
- `patch()`
- `delete()`
- `options()`

Each method accepts two parameters:
1. Your controller class
2. Controller method name

### Route Scopes

Define multiple HTTP methods for a single endpoint:

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint')
    ->scope(function ($route) {
        $route->post(YourController::class, 'methodNameForGet');
        $route->post(YourController::class, 'methodNameForPost');
    });
```

Or directly:

```php
$route = \GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint');
$route->post(YourController::class, 'methodNameForGet');
$route->post(YourController::class, 'methodNameForPost');
```

### Route Groups

Register multiple HTTP methods to a single controller method:

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint')
    ->group(YourController::class, 'methodNameForGetPost', ['get', 'post']);
```

**Parameters:**
1. Controller class
2. Controller method
3. Array of HTTP methods

### Route Callbacks

Register inline callbacks as route actions:

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->route('/your-endpoint')
    ->callback(['get'], function ($request) {
        return ['say' => 'I am callback get'];
    });
```

Or pass a single method as a string:

```php
->callback('get', function ($request) {
    return ['say' => 'I am callback get'];
});
```

The callback function receives a single parameter: the `WP_REST_Request` object.

### Middlewares

You can add middlewares at different levels (namespace, route, or action):

```php
\GiApiRoute\Route::namespaceGroup('yourslug/v1')
    ->middlewares(['permission' => ['nonce']])
    ->route('/testing')
    ->middlewares(['before' => [new Middleware()]])
    ->post(\GiApiRoute\Test\TestController::class, 'post')
    ->middlewares(['after' => [Middleware::class]]);
```

#### Middleware Types

The `middlewares()` method accepts an array with the following keys:

- **`permission`** - Validates access to the resource
- **`before`** - Executes before the main controller request
- **`after`** - Executes after the controller processes the request

Each middleware array can contain either class names or class instances or aliases.

#### Built-in Aliases

**Permission Middlewares:**
- `'nonce'` - Validates WordPress REST API nonces
    - Customize action: `'nonce:your_action_name'`
    - Requires header: `X-WP-Nonce` with value from `wp_create_nonce('wp_rest')` or `wp_create_nonce('your_action_name')`
- `'auth'` - User authentication check
- `'capability'` - Capability check with optional specification: `'capability:edit_posts,delete_posts'`
- `'role'` - Role check with optional specification: `'role:editor,administrator'`

**Before Middlewares:**
- `'throttle'` - Rate limiting with parameters: `'throttle:10;60'`
    - First parameter: Maximum requests allowed
    - Second parameter: Time window in seconds

#### Validation Middleware

The module includes `ValidatorMiddleware` which uses the Respect\Validation library. See the [Respect\Validation documentation](https://respect-validation.readthedocs.io/en/2.4/) for available validators.

```php
->middlewares([
    'before' => [
        new ValidatorMiddleware([
            'email' => \Respect\Validation\Validator::email(),
            'age' => respectV()::intVal()->min(18)
        ])
    ]
]);
```

Or create validators in advance:

```php
$createPostValidator = new ValidatorMiddleware([
    'email' => respectV()::email(),
    'age' => \Respect\Validation\Validator::intVal()->min(18)
]);

->middlewares(['before' => [$createPostValidator]]);
```

#### Direct Validation

You can also validate data directly in your controller or elsewhere using:

```php
$number = 123;
\Respect\Validation\Validator::numericVal()->isValid($number);
// or
respectV()::numericVal()->isValid($number);
```

#### Middleware Scope Levels

- **Namespace level** - Affects all nested route endpoints
- **Route level** - Affects all controller methods for that route
- **Action level** - Affects only the specific method

### Custom Middlewares

To create a custom middleware, implement one of these interfaces:

- `GiApiRoute\Contracts\PermissionMiddlewareInterface`
- `GiApiRoute\Contracts\BeforeMiddlewareInterface`
- `GiApiRoute\Contracts\AfterMiddlewareInterface`

Each interface requires implementing the `handle()` method:

```php
public function handle(WP_REST_Request $request, callable $next): mixed
```

For `after` middlewares, the signature is:

```php
public function handle(WP_REST_Request $request, mixed $response, callable $next): mixed
```

The `handle()` method should either return a value or call `$next($request)`:

```php
public function handle(WP_REST_Request $request, callable $next): mixed
{
    if (!is_user_logged_in()) {
        return false; // Block unauthenticated requests
    }
    
    return $next($request); // Continue to next middleware
}
```

Use `WP_Error` to return detailed error information:

```php
return new WP_Error('access_denied', 'User does not have permission', ['status' => 403]);
```

---
