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
 * "alias:param1,param2,...".
 *
 * Example:
 * ```php
 * $manager = new MiddlewareManager();
 * $manager->resolveOne('before', 'auth:admin,1');
 * ```
 *
 * @package gi_api_route\Support
 * @author Artem <gammaironak@gmail.com>
 * @date 02.12.2025
 */

namespace gi_api_route\Support;

use gi_api_route\Contracts\AfterMiddlewareInterface;
use gi_api_route\Contracts\MiddlewareInterface;
use gi_api_route\Enums\MiddlewareType;
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
            MiddlewareType::PERMISSION->name => [],
            MiddlewareType::BEFORE->name     => [],
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
     * Resolve middleware from an alias string.
     *
     * Format: "alias" OR "alias:param1,param2,..."
     *
     * @param MiddlewareType $type Middleware type
     * @param string $definition Alias string with optional parameters
     * @return MiddlewareInterface|AfterMiddlewareInterface
     *
     * @throws InvalidArgumentException
     */
    public function resolveFromAlias(MiddlewareType $type, string $definition): MiddlewareInterface|AfterMiddlewareInterface
    {
        // Format: "alias" OR "alias:param1,param2"
        [$alias, $paramString] = array_pad(explode(':', $definition, 2), 2, null);

        if (!isset($this->aliases[$type->name][$alias])) {
            throw new InvalidArgumentException("Unknown middleware alias: $alias");
        }

        $class = $this->aliases[$type->name][$alias];
        $params = $paramString ? explode(',', $paramString) : [];

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
