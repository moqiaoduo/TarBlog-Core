<?php

namespace TarBlog\Facades;

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
 * @method static \TarBlog\Routing\PendingResourceRegistration apiResource(string $name, string $controller, array $options = [])
 * @method static void apiResources(array $resources)
 * @method static \TarBlog\Routing\RouteRegistrar middleware(array|string|null $middleware)
 * @method static \TarBlog\Routing\Route substituteBindings(\TarBlog\Facades\Route $route)
 * @method static void substituteImplicitBindings(\TarBlog\Facades\Route $route)
 * @method static \TarBlog\Routing\RouteRegistrar domain(string $value)
 * @method static \TarBlog\Routing\RouteRegistrar name(string $value)
 * @method static \TarBlog\Routing\RouteRegistrar namespace(string $value)
 * @method static \TarBlog\Routing\Router|\TarBlog\Routing\RouteRegistrar group(array|\Closure|string $attributes, \Closure|string $routes)
 * @method static \TarBlog\Routing\Route redirect(string $uri, string $destination, int $status = 302)
 * @method static \TarBlog\Routing\Route permanentRedirect(string $uri, string $destination)
 * @method static \TarBlog\Routing\Route view(string $uri, string $view, array $data = [])
 * @method static void bind(string $key, string|callable $binder)
 * @method static void model(string $key, string $class, \Closure|null $callback = null)
 * @method static \TarBlog\Routing\Route current()
 * @method static string|null currentRouteName()
 * @method static string|null currentRouteAction()
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
