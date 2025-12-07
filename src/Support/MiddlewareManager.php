<?php
/**
 * Manages middleware resolution for REST API routes.
 *
 * This class supports resolving middlewares from:
 * - Direct object instances
 * - Class strings
 * - Aliases defined in the manager
 *
 * Aliases can optionally include constructor parameters in the format:
 * "alias:param1;param2;...".
 *
 * Example:
 * ```php
 * $manager = new MiddlewareManager();
 * $manager->resolveOne('before', 'auth:admin;1');
 * ```
 *
 * @package GiApiRoute\Support
 * @author Artem <gammaironak@gmail.com>
 * @date 02.12.2025
 */

namespace GiApiRoute\Support;

use GiApiRoute\Contracts\AfterMiddlewareInterface;
use GiApiRoute\Contracts\MiddlewareInterface;
use GiApiRoute\Enums\MiddlewareType;
use GiApiRoute\Middlewares\Before\ThrottleMiddleware;
use GiApiRoute\Middlewares\Permission\AuthMiddleware;
use GiApiRoute\Middlewares\Permission\CapabilityMiddleware;
use GiApiRoute\Middlewares\Permission\NonceMiddleware;
use GiApiRoute\Middlewares\Permission\RoleMiddleware;
use InvalidArgumentException;

class MiddlewareManager
{
    /**
     * Middleware aliases by type.
     *
     * Format:
     * [
     *   'permission' => [ 'logger' => LoggerMW::class ],
     *   'before'     => [ 'auth'   => AuthMW::class ],
     *   'after'      => [ 'tracker'=> ResponseTrackerMW::class ]
     * ]
     *
     * @var array<string, array<string, class-string>>
     */
    protected array $aliases;

    /**
     * Initialize MiddlewareManager with empty aliases.
     */
    public function __construct()
    {
        $this->aliases = [
            MiddlewareType::PERMISSION->name => [
                'auth'       => AuthMiddleware::class,
                'capability' => CapabilityMiddleware::class,
                'role'       => RoleMiddleware::class,
                'nonce'      => NonceMiddleware::class
            ],
            MiddlewareType::BEFORE->name     => [
                'throttle' => ThrottleMiddleware::class
            ],
            MiddlewareType::AFTER->name      => []
        ];
    }



    /**
     * Resolve a list of middleware definitions.
     *
     * Each element can be:
     *  - An object implementing the correct interface
     *  - A class string
     *  - An alias with optional parameters
     *
     * @param MiddlewareType $type
     * @param array $definitions
     * @return list<MiddlewareInterface|AfterMiddlewareInterface>
     */
    public function resolveList(MiddlewareType $type, array $definitions): array
    {
        $result = [];

        foreach ($definitions as $def) {
            $result[] = $this->resolveOne($type, $def);
        }

        return $result;
    }


    /**
     * Resolve a single middleware definition.
     *
     * @param MiddlewareType $type
     * @param mixed $definition
     * @return MiddlewareInterface|AfterMiddlewareInterface
     *
     * @throws InvalidArgumentException
     */
    public function resolveOne(MiddlewareType $type, mixed $definition): MiddlewareInterface|AfterMiddlewareInterface
    {
        // Already object
        if (is_object($definition)) {
            $this->validateInstance($definition, $type);
            return $definition;
        }

        // Class-string
        if (is_string($definition) && class_exists($definition)) {
            return $this->instantiate($definition, $type);
        }

        // Try Alias
        if ( is_string($definition) ) {
            return $this->resolveFromAlias($type, $definition);
        }

        throw new InvalidArgumentException(
            'Cannot resolve middleware definition: ' . print_r($definition, true)
        );
    }


    /**
     *Resolve middleware from alias with support for multiple parameter groups.
     *
     * Format examples:
     *    "alias"                          => no parameters
     *    "alias:param1,param2"             => single parameter group
     *    "alias:param1,param2;param3,param4" => multiple parameter groups
     *
     * @param MiddlewareType $type Middleware type
     * @param string $definition Alias string with optional parameters
     * @return MiddlewareInterface|AfterMiddlewareInterface
     *
     * @throws InvalidArgumentException
     */
    public function resolveFromAlias(MiddlewareType $type, string $definition): MiddlewareInterface|AfterMiddlewareInterface
    {

        [$alias, $paramString] = array_pad(explode(':', $definition, 2), 2, null);

        if (!isset($this->aliases[$type->name][$alias])) {
            throw new InvalidArgumentException("Unknown middleware alias: $alias");
        }

        $class = $this->aliases[$type->name][$alias];
        $params = [];

        if ($paramString) {
            $groups = explode(';', $paramString);

            foreach ($groups as $group) {
                $groupParams = array_map('trim', explode(',', $group));
                // Flatten single-element groups to scalar
                $params[] = count($groupParams) === 1 ? $groupParams[0] : $groupParams;
            }
        }

        $instance = new $class(...$params);
        $this->validateInstance($instance, $type);

        return $instance;
    }

    /**
     * Instantiate a middleware class string.
     *
     * @param string $class Class name
     * @param MiddlewareType $type Middleware type
     * @return MiddlewareInterface|AfterMiddlewareInterface
     *
     * @throws InvalidArgumentException
     */
    private function instantiate(string $class, MiddlewareType $type): MiddlewareInterface|AfterMiddlewareInterface
    {
        $instance = new $class();
        $this->validateInstance($instance, $type);
        return $instance;
    }


    /**
     * Validate that the object implements the correct interface for its type.
     *
     * @param object $instance Middleware instance
     * @param MiddlewareType $type
     *
     * @throws InvalidArgumentException
     */
    private function validateInstance(object $instance, MiddlewareType $type): void
    {
        $isBefore = $instance instanceof MiddlewareInterface;
        $isAfter = $instance instanceof AfterMiddlewareInterface;

        $valid = match ($type) {
            MiddlewareType::PERMISSION, MiddlewareType::BEFORE => $isBefore,
            MiddlewareType::AFTER                => $isAfter
        };

        if (!$valid) {
            throw new InvalidArgumentException(
                "$type->name middleware must implement " .
                ($type === MiddlewareType::AFTER
                    ? AfterMiddlewareInterface::class
                    : MiddlewareInterface::class)
            );
        }
    }
}
