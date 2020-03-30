<?php

namespace TarBlog\Routing;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class RouteCollection implements Countable, IteratorAggregate
{
    /**
     * @var Route[]
     */
    protected $routes = [];

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $nameList = [];

    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * @return Route[]
     */
    public function getRoutes()
    {
        return array_values($this->routes);
    }

    /**
     * @param Route[] $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    public function add(Route $route)
    {
        $this->addToCollections($route);

        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param  \TarBlog\Routing\Route  $route
     * @return void
     */
    protected function addToCollections($route)
    {
        $this->routes[] = $route;
    }

    /**
     * Refresh the name look-up table.
     *
     * This is done in case any names are fluently defined or if routes are overwritten.
     *
     * @return void
     */
    public function refreshNameLookups()
    {
        $this->nameList = [];

        foreach ($this->routes as $route) {
            if ($route->getName()) {
                $this->nameList[$route->getName()] = $route;
            }
        }
    }

    /**
     * 通过名称获取路由
     *
     * @param $name
     * @return Route|null
     */
    public function getByName($name)
    {
        return $this->nameList[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }
}