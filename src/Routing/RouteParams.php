<?php

namespace TarBlog\Routing;

use ArrayAccess;
use Exception;

class RouteParams implements ArrayAccess
{
    /**
     * 路由参数
     *
     * @var array
     */
    protected $params;

    /**
     * RouteParams constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->params = $params;
    }

    /**
     * 魔术方法，用于获取参数
     *
     * @param $name
     * @return mixed|void
     */
    public function __get($name)
    {
        if (isset($this->params[$name]))
            return $this->params[$name];
    }


    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Unable to modify data in ".self::class);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Unable to delete data in ".self::class);
    }
}