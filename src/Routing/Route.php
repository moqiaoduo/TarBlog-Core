<?php

namespace TarBlog\Routing;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

class Route
{
    use Macroable;

    /**
     * 路由名称
     *
     * @var string
     */
    protected $name;

    /**
     * 路由Uri
     *
     * @var string
     */
    protected $uri;

    /**
     * 路由动作
     *
     * @var string
     */
    protected $action;

    /**
     * 路由HTTP方法
     *
     * @var array
     */
    protected $methods;

    /**
     * 路由表达式控制
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * 中间件
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * 路由参数
     *
     * @var array
     */
    public $parameters;

    /**
     * 支持多层路径的参数
     * 例如directory等
     * 需要路由进行设定
     *
     * @var array
     */
    protected $multiDivParams = [];

    /**
     * The computed gathered middleware.
     *
     * @var array|null
     */
    public $computedMiddleware;

    /**
     * The router instance used by the route.
     *
     * @var \TarBlog\Routing\Router
     */
    protected $router;

    /**
     * The container instance used by the route.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Route constructor.
     * @param string $uri
     * @param string $action
     * @param array $methods
     */
    public function __construct($uri, $action, $methods)
    {
        $this->uri = $uri;
        $this->action = $action;
        $this->methods = $methods;
    }

    /**
     * 设置app container
     *
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the router instance on the route.
     *
     * @param  \TarBlog\Routing\Router  $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * 获取路由Uri
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * 设置路由Uri
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = trim($uri);
    }

    /**
     * 路由参数正则表达式限定
     *
     * @param array $patterns
     */
    public function where($patterns = [])
    {
        $this->wheres = array_merge($this->wheres, $patterns);
    }

    /**
     * 获取使用正则表达式限定的参数
     *
     * @return array
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * 设置支持多级目录的参数
     *
     * @param $params
     * @return $this|array
     */
    public function multiDiv($params = null)
    {
        if ($params === null) {
            return $this->multiDivParams;
        }

        $this->multiDivParams = array_merge($this->multiDivParams, Arr::wrap($params));

        return $this;
    }

    /**
     * 判断是否为支持多级目录的参数
     *
     * @param $param
     * @return bool
     */
    public function isMultiDivParam($param)
    {
        return in_array($param, $this->multiDivParams);
    }

    /**
     * Uri匹配，不匹配返回false，匹配对应参数
     *
     * @param $uri
     * @return boolean
     */
    public function uriMatch($uri)
    {
        $out_uri = $uri;

        $params = $this->getParamsFromPatternUri();

        // 先去除路由uri开通的/
        $in_uri = substr($this->uri,0,1) == '/' ? substr($this->uri,1) : $this->uri;
        // 再去除路由uri末尾的/
        $in_uri = substr($in_uri,-1) == '/' ? substr($in_uri,0,strlen($in_uri)-1) : $in_uri;
        // 再将.替换为\. （.在正则中匹配任意一个字符，为了匹配.，必须换为\.）
        $in_uri = str_replace('.','\.',$in_uri);

        $pattern = $this->getPattern($in_uri,$params) ?: '/'; // 为了匹配 /

        // 匹配uri并获取参数
        preg_match("`^$pattern$`",$out_uri,$matches);

        // 未匹配到时，返回false
        if (count($matches) == 0) return false;

        // 去除第一个结果，第一个结果是uri本身
        array_shift($matches);

        for($i=0;$i<count($matches);$i++) {
            // 判断路由是否符合正则表达式，之前考虑第一遍匹配就过滤的，但是出了点问题，所以改为二次匹配
            if (! $this->ifParamsMatch($params[$i],$matches[$i])) return false;

            // 多级目录参数，按 / 分割成数组
            if ($isMultiDivParam = $this->isMultiDivParam($params[$i])) {
                $matches[$i] = explode("/",$matches[$i]);
            }

            // 假如匹配到的参数中间有/，但是不是多级目录{directory}的话，理应为不匹配的路由
            if (strpos($matches[$i],"/")!==false && !$isMultiDivParam) return false;

        }
        $this->parameters = array_combine($params,$matches);
        return true;
    }

    /**
     * 判断路由是否符合正则表达式
     *
     * @param $param
     * @param $value
     * @return bool
     */
    protected function ifParamsMatch($param, $value)
    {
        if (isset($this->wheres[$param])) {
            preg_match('/'.$this->wheres[$param].'/',$value,$match);
            if (count($match) == 0) return false;
        }
        return true;
    }

    /**
     * 匹配参数
     *
     * @return array|mixed
     */
    protected function getParamsFromPatternUri()
    {
        preg_match_all('/\{(.*?)\}/',$this->uri,$matches);
        return empty($matches)?[]:$matches[1];
    }

    /**
     * 获取路由uri对应的正则表达式
     *
     * @param $uri
     * @param $params
     * @return string|string[]
     */
    protected function getPattern($uri,$params)
    {
        foreach ($params as $param) {
            $optional = substr($param,0,1) == '?';
            $pattern = $optional?'.*':'.+';
            if ($optional)
                $uri = str_replace('/{'.$param.'}','/?('.$pattern.')',$uri);
            $uri = str_replace('{'.$param.'}','('.$pattern.')',$uri);
        }
        return $uri;
    }

    /**
     * 获取路由动作
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 设置路由动作
     *
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * 获取名称
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 设置路由名称
     *
     * @param $name
     * @return Route
     */
    public function name($name)
    {
        $this->name=$this->addGroupName($name);

        return $this;
    }

    protected function addGroupName($name)
    {
        $groupStack = last($this->router->getGroupStack());

        if (isset($groupStack['as'])) {
            return $groupStack['as'] . $name;
        }

        return $name;
    }

    /**
     * 获取路由HTTP方法
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * 设置路由HTTP方法
     *
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * 判断路由HTTP方法是否相符
     *
     * @param $method
     * @return bool
     */
    public function isMethod($method)
    {
        foreach ($this->methods as $m) {
            if (strtolower($m) == strtolower($method)) return true;
        }
        return false;
    }

    /**
     * 获取或添加中间件
     *
     * @param null $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array) ($this->middleware ?? []);
        }

        if (is_string($middleware)) {
            $middleware = func_get_args();
        }

        $this->middleware = array_merge(
            (array) ($this->middleware ?? []), $middleware
        );

        return $this;
    }

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        if (! is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }

        $this->computedMiddleware = [];

        return $this->computedMiddleware = array_unique($this->middleware(), SORT_REGULAR);
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run()
    {
        $this->container = $this->container ?: new Container;

        try {
            $action = $this->action;

            if (is_callable($action)) {
                // 当action为匿名函数时，直接调用
                return $this->runAction($action);
            } elseif (strpos($action,'@') !== false) {
                // 假如action为controller@method，初始化后调用
                [$controller, $method] = explode('@',$action,2);
                if (!class_exists($controller))
                    throw new \InvalidArgumentException("Controller [$controller] is not exist.");
                return $this->runController(new $controller, $method);
            } else {
                if (!class_exists($action))
                    throw new \InvalidArgumentException("Action [{$this->action}] is not exist.");
                return $this->runController(new $action);
            }
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * 运行Action（函数）
     *
     * @param $action
     * @return mixed
     *
     * @throws
     */
    protected function runAction($action)
    {
        $reflect = new \ReflectionFunction($action);

        $params = $this->bindParamAndMerge($reflect, array_values($this->parameters));

        $params = array_merge($params, array_values($this->parameters));

        return $action(...$params);
    }

    /**
     * 运行Controller（控制器）
     *
     * @param $action
     * @param null $method
     * @return mixed
     * @throws
     */
    protected function runController($action, $method = null)
    {
        $reflect = new \ReflectionClass($action);

        if ($method === null) {
            if (! $reflect->hasMethod('__invoke'))
                throw new \InvalidArgumentException(
                    "Action [{$reflect->getName()}] does not have [__invoke] method.");

            $methodReflect = $reflect->getMethod('__invoke');
        } else {
            if (! $reflect->hasMethod($method))
                throw new \InvalidArgumentException(
                    "Controller [{$reflect->getName()}] does not have [$method] method.");

            $methodReflect = $reflect->getMethod($method);
        }

        $params = $this->bindParamAndMerge($methodReflect, array_values($this->parameters));

        if ($method === null)
            return $action(...$params);
        else
            return $action->$method(...$params);
    }

    /**
     * 绑定控制器参数
     *
     * @param \ReflectionMethod|\ReflectionFunction $reflect
     * @param array $normalParam
     * @return array
     * @throws
     */
    protected function bindParamAndMerge($reflect, $normalParam = [])
    {
        $params = [];

        $defaultParams = array_values($this->parameters);

        $paramOffset = 0;

        foreach ($reflect->getParameters() as $parameter) {
            if (! $parameter->hasType()) break; // 按照规定，所有特殊参数应放在最前面，且普通参数不得带类型

            $type = $parameter->getType();

            if ($type == Request::class) {
                $params[] = $this->router->getCurrentRequest();
            } elseif ($type == RouteParams::class) {
                $params[] = new RouteParams($this->parameters);
            } elseif (class_exists($type)) {
                $class = (string) $type;
                $reflectClass = new \ReflectionClass($class);
                if ($reflectClass->isInstantiable() && $reflectClass->isSubclassOf(Model::class)) {
                    $index = $parameter->getPosition() - $paramOffset;
                    if (! isset($defaultParams[$index])) continue;
                    $id = $defaultParams[$index]; // 不知道这样会不会浪费性能，先这样写
                    unset($defaultParams[$index]);
                    $model = $class::findOrFail($id);
                    $params[] = $model;
                    $paramOffset++;
                }
            }
        }

        return array_merge($params, $defaultParams);
    }

    public function __sleep()
    {
        return ['name','uri','action','methods','wheres','middleware','parameters','multiDivParams'];
    }
}