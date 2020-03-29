<?php

namespace TarBlog\View;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Support\Arr;

class Factory implements FactoryContract
{
    /**
     * 主题名称（目录）
     *
     * @var string
     */
    protected $theme;

    /**
     * 共享数据
     *
     * @var array
     */
    protected $shares = [];

    /**
     * 命名空间（代替路径）
     * 不在命名空间内的视图将从主题目录中加载
     *
     * @var array
     */
    protected $namespace = [];

    /**
     * Create a new view factory instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Dispatcher $events)
    {
        $this->share('__env', $this);
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @inheritDoc
     */
    public function exists($view)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function file($path, $data = [], $mergeData = [])
    {}

    /**
     * @inheritDoc
     * @throws ViewNotFoundException
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData , $this->shares, $this->parseData($data));

        $theme = $this->shares['app']->resourcePath($this->theme);

        $viewToPath = str_replace(".","/",$view); // view的点表示路径分隔，虽然很少用到但还是支持一下吧

        if (($pos = strpos($view,"::")) !== false &&
            array_key_exists(($namespace = substr($view,0,$pos)),$this->namespace)) {
            $path = $this->namespace[$namespace];

            $file = str_replace('.',DIRECTORY_SEPARATOR , substr($viewToPath,$pos+2)) . '.php';

            // 假如命名空间是errors，则优先读取主题中的错误页面设置
            if ($namespace != 'errors' || ! file_exists($file = $theme . DIRECTORY_SEPARATOR . $file)) {
                $theme = $this->findViewFileOfDir($path,$file,$view);

                $file = $theme . $file;
            }
        } else {
            $file = $theme . DIRECTORY_SEPARATOR . $viewToPath . '.php';
        }

        $view = new View($file, $view, $data);

        $view->setThemeDir($theme);

        return $view;
    }

    /**
     * 选择视图目录
     *
     * @param $dirs
     * @param $file
     * @param $view
     * @return string
     * @throws ViewNotFoundException
     */
    protected function findViewFileOfDir($dirs, $file, $view)
    {
        foreach ($dirs as $dir) {
            $dir = substr($dir,-1)===DIRECTORY_SEPARATOR ? $dir : $dir.DIRECTORY_SEPARATOR;

            if (file_exists($dir.$file))
                return $dir;
        }

        throw new ViewNotFoundException("View [$view] does not exist.");
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * @inheritDoc
     */
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->shares = array_merge($this->shares, $this->parseData($key));
        } else {
            $this->shares[$key] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function composer($views, $callback)
    {}

    /**
     * @inheritDoc
     */
    public function creator($views, $callback)
    {}

    /**
     * @inheritDoc
     */
    public function addNamespace($namespace, $hints)
    {
        $this->namespace[$namespace] = array_merge($this->namespace[$namespace] ?? [], Arr::wrap($hints));
    }

    /**
     * @inheritDoc
     */
    public function replaceNamespace($namespace, $hints)
    {
        $this->namespace[$namespace] = $hints;
    }
}