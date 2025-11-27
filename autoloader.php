<?php
/**
 * @author Artem <gammaironak@gmail.com>
 * @date 19.11.2025
 */

final class GI_ApiRouteAutoloader
{

    /**
     * [namespace => rootDir]
     * @var array<string, string>
     */
    protected array $namespaceMap = [];

    /**
     * Add namespace mapping
     */
    public function addNamespace(string $namespace, string $rootDir): bool
    {

        if (is_dir($rootDir)) {
            $this->namespaceMap[rtrim($namespace, '\\')] = rtrim($rootDir, '/\\');
            return true;
        }
        return false;
    }

    /**
     * Register autoloader
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
    }

    protected function autoload(string $class): void
    {

        foreach ($this->namespaceMap as $namespace => $rootDir) {
            if (str_starts_with($class, $namespace . '\\')) {
                $relativeClass = substr($class, strlen($namespace) + 1);
                $filePath = $rootDir . '/' . str_replace('\\', '/', $relativeClass) . '.php';

                if (is_file($filePath)) {
                    require $filePath;
                }
                return;
            }
        }
    }
}

$autoloader = new GI_ApiRouteAutoloader();

// Register Namespace
$autoloader->addNamespace('gi_api_route', __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
$autoloader->register();

//Require helper functions
require_once 'functions.php';

