<?php

namespace TarBlog\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \TarBlog\Routing\Route get(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route post(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route put(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route delete(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route patch(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route options(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route any(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\Route match(array|string $methods, string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \TarBlog\Routing\RouteRegistrar prefix(string  $prefix)
 * @method static \TarBlog\Routing\RouteRegistrar where(array  $where)
 * @method static \TarBlog\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method static \TarBlog\Routing\RouteRegistrar middleware(array|string|null $middleware)
 * @method static \TarBlog\Routing\RouteRegistrar name(string $value)
 * @method static \TarBlog\Routing\RouteRegistrar namespace(string $value)
 * @method static \TarBlog\Routing\Router|\TarBlog\Routing\RouteRegistrar group(array|\Closure|string $attributes, \Closure|string $routes)
 * @method static \TarBlog\Routing\RouteCollection getRoutes()
 *
 * @see \TarBlog\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}
