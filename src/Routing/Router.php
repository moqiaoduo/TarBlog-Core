<?php

namespace TarBlog\Routing;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @mixin \TarBlog\Routing\RouteRegistrar
 */
class Router implements RegistrarContract
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The route collection instance.
     *
     * @var \TarBlog\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The request currently being dispatched.
     *
     * @var \Illuminate\Http\Request
     */
    protected $currentRequest;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    public $middlewarePriority = [];

    /**
     * The globally available parameter patterns.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * All of the verbs supported by the router.
     *
     * @var array
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * The attributes that can be set through this class.
     *
     * @var array
     */
    protected $allowedAttributes = [
        'as', 'middleware', 'name', 'namespace', 'prefix', 'where',
    ];

    /**
     * Create a new Router instance.
     *
     * @param Dispatcher $events
     * @param Container|null  $container
     * @return void
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        $this->events = $events;
        $this->routes = new RouteCollection;
        $this->container = $container ?: new Container;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param RouteCollection $routes
     */
    public function setRoutes(RouteCollection $routes): void
    {
        foreach ($routes as $route) {
            $route->setRouter($this)->setContainer($this->container);
        }

        $this->routes = $routes;

        $this->container->instance('routes', $this->routes);
    }

    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    protected function addRoute($methods, $uri, $action)
    {
        $stack = last($this->groupStack);
        // 检查属性是否允许添加
        foreach ($stack as $key=>$value) {
            if (!in_array($key,$this->allowedAttributes))
                throw new \InvalidArgumentException("Not allowed route attribute [$key]");
        }

        $route = $this->newRoute(Arr::wrap($methods),$uri,$action);

        $route->setAction(is_string($route->getAction()) ?
            $this->addGroupNamespaceToStringUses($route->getAction()) : $route->getAction());

        $this->addWhereClausesToRoute($route);

        $this->addGroupPrefixToRoute($route);

        $this->addGroupMiddlewareToRoute($route);

        $this->addGroupWheresToRoute($route);

        return $this->routes->add($route);
    }

    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \TarBlog\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($uri, $action, $methods))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Add the necessary where clauses to the route based on its initial registration.
     *
     * @param  \TarBlog\Routing\Route  $route
     * @return \TarBlog\Routing\Route
     */
    protected function addWhereClausesToRoute($route)
    {
        $route->where(array_merge(
            $this->patterns, $route->getWheres() ?? []
        ));

        return $route;
    }

    /**
     * @param  \TarBlog\Routing\Route  $route
     * @return \TarBlog\Routing\Route
     */
    protected function addGroupPrefixToRoute($route)
    {
        $groupStack = last($this->groupStack);

        if (isset($groupStack['prefix'])) {
            $route->setUri(preg_replace('#(/)\\1+#','$1',$groupStack['prefix'].'/'.$route->uri()));
        }

        return $route;
    }

    protected function addGroupMiddlewareToRoute(Route $route)
    {
        $groupStack = last($this->getGroupStack());

        if (isset($groupStack['middleware'])) {
            $route->middleware($groupStack['middleware']);
        }

        return $route;
    }

    protected function addGroupWheresToRoute(Route $route)
    {
        $groupStack = last($this->getGroupStack());

        if (isset($groupStack['where'])) {
            $route->where($groupStack['where']);
        }

        return $route;
    }

    /**
     * Parse a string based action for the "uses" fluent method.
     *
     * @param  string  $action
     * @return string
     */
    protected function addGroupNamespaceToStringUses($action)
    {
        $groupStack = last($this->getGroupStack());

        if (isset($groupStack['namespace']) && strpos($action, '\\') !== 0) {
            return $groupStack['namespace'].'\\'.$action;
        }

        return $action;
    }

    /**
     * Register a new GET route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function get($uri, $action = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action)
            ->setContainer($this->container);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * @inheritDoc
     */
    public function resource($name, $controller, array $options = [])
    {
        if ($this->container && $this->container->bound(ResourceRegistrar::class)) {
            $registrar = $this->container->make(ResourceRegistrar::class);
        } else {
            $registrar = new ResourceRegistrar($this);
        }

        return new PendingResourceRegistration(
            $registrar, $name, $controller, $options
        );
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function any($uri, $action = null)
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }

    /**
     * Register a new route with the given verbs.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  \Closure|array|string|callable|null  $action
     * @return \TarBlog\Routing\Route
     */
    public function match($methods, $uri, $action = null)
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure|string  $routes
     * @return void
     */
    public function group(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);

        // Once we have updated the group stack, we'll load the provided routes and
        // merge in the group's attributes when the routes are created. After we
        // have created the routes, we will pop the attributes off the stack.
        $this->loadRoutes($routes);

        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param  array  $new
     * @return array
     */
    public function mergeWithLastGroup($new)
    {
        return RouteGroup::merge($new, end($this->groupStack));
    }

    /**
     * Load the provided routes.
     *
     * @param  \Closure|string  $routes
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            (new RouteFileRegistrar($this))->register($routes);
        }
    }

    /**
     * @inheritDoc
     */
    public function substituteBindings($route)
    {}

    /**
     * @inheritDoc
     */
    public function substituteImplicitBindings($route)
    {}

    /**
     * Register a group of middleware.
     *
     * @param  string  $name
     * @param  array  $middleware
     * @return $this
     */
    public function middlewareGroup($name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Register a short-hand name for a middleware.
     *
     * @param  string  $name
     * @param  string  $class
     * @return $this
     */
    public function aliasMiddleware($name, $class)
    {
        $this->middleware[$name] = $class;

        return $this;
    }

    /**
     * 获取当前Request
     *
     * @return Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * Dispatch the request to the application.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException
     */
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }

    /**
     * Dispatch the request to a route and return the response.
     * 由于原版Route并不支持重用，所以match放在了Collection，
     * 显然这并不适合本项目，所以遍历放到了外边，也就是这里
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException
     */
    public function dispatchToRoute(Request $request)
    {
        $notMatchMethod = [];
        $methodNotMatchUri = null;
        foreach ($this->routes->getRoutes() as $route) {
            if ($route->uriMatch($request->path())) {
                if ($route->isMethod($request->method())) {
                    $this->container->instance(Route::class, $route);
                    $response = $this->runRoute($request, $route);
                    if ($response->getStatusCode() !== 404) {
                        $methodNotMatchUri = null;
                        break;
                    }
                } else {
                    if ($route->uri() == $methodNotMatchUri) {
                        $notMatchMethod = $notMatchMethod + $route->getMethods();
                    } else {
                        $notMatchMethod = $route->getMethods();
                        $methodNotMatchUri = $route->uri();
                    }
                }
            }
        }

        if ($methodNotMatchUri !== null) {
            throw new MethodNotAllowedHttpException(array_unique($notMatchMethod));
        }

        if (!isset($response))
            throw new NotFoundHttpException;

        return $response;
    }

    /**
     * Return the response for the given route.
     *
     * @param Request $request
     * @param Route $route
     * @return Response
     * @throws BindingResolutionException
     */
    protected function runRoute(Request $request, Route $route)
    {
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return $this->prepareResponse($request,
            $this->runRouteWithinStack($route, $request)
        );
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param Route $route
     * @param Request $request
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function runRouteWithinStack(Route $route, Request $request)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
            $this->container->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);

        return (new Pipeline($this->container))
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($route) {
                return $this->prepareResponse(
                    $request, $route->run()
                );
            });
    }

    /**
     * Gather the middleware for the given route with resolved class names.
     *
     * @param  Route  $route
     * @return array
     */
    public function gatherRouteMiddleware(Route $route)
    {
        $middleware = collect($route->gatherMiddleware())->map(function ($name) {
            return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        })->flatten();

        return $this->sortMiddleware($middleware);
    }

    /**
     * Sort the given middleware by priority.
     *
     * @param Collection $middlewares
     * @return array
     */
    protected function sortMiddleware(Collection $middlewares)
    {
        return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
    }

    /**
     * Create a response instance from the given value.
     *
     * @param  Request  $request
     * @param  mixed  $response
     * @return Response
     */
    public function prepareResponse($request, $response)
    {
        return static::toResponse($request, $response);
    }

    /**
     * Static version of prepareResponse.
     *
     * @param  Request  $request
     * @param  mixed  $response
     * @return Response
     */
    public static function toResponse($request, $response)
    {
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        if (! $response instanceof Response) {
            $response = new Response($response);
        }

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($method === 'middleware') {
            return (new RouteRegistrar($this))->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
        }

        return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
    }
}