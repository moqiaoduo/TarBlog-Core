<?php

namespace TarBlog\View;

use ArrayAccess;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;

class View implements ArrayAccess, Htmlable, ViewContract
{
    /**
     * 视图名称
     *
     * @var string
     */
    private $view;

    /**
     * 视图文件
     *
     * @var string
     */
    private $file;

    /**
     * 备用视图
     *
     * @var string
     */
    private $fallback;

    /**
     * 视图数据
     *
     * @var array
     */
    private $data;

    /**
     * 主题目录
     *
     * @var string
     */
    private $themeDir;

    /**
     * 视图引擎
     *
     * @var Engine
     */
    private $engine;

    /**
     * View constructor.
     * @param $file
     * @param $view
     * @param array $data
     */
    public function __construct($file, $view, array $data = [])
    {
        $this->file = $file;
        $this->view = $view;
        $this->data = $data;
        $this->engine = new Engine($this);
    }

    /**
     * 获取主题路径
     *
     * @return mixed
     */
    public function getThemeDir()
    {
        return $this->themeDir;
    }

    /**
     * 设置主题路径
     *
     * @param mixed $themeDir
     * @return View
     */
    public function setThemeDir($themeDir)
    {
        $this->themeDir = $themeDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFallbackFile(): string
    {
        return $this->themeDir . DIRECTORY_SEPARATOR . $this->fallback;
    }

    /**
     * @return string
     */
    public function getFallback(): string
    {
        return $this->fallback;
    }

    /**
     * @param string $fallback
     * @return View
     */
    public function setFallback(string $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * 添加宏指令，用于模板调用
     *
     * @param $name
     * @param null $macro
     * @throws
     */
    public function addMicro($name, $macro = null)
    {
        if (is_null($macro)) {
            Engine::mixin($name);
        }

        Engine::macro($name, $macro);
    }

    /**
     * 设置页面标题
     *
     * @param $title
     * @return $this
     */
    public function title($title)
    {
        $this->engine->setTitle($title);

        return $this;
    }

    /**
     * 设置页面类型
     *
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->engine->setType($type);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return $this->engine->render();
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return $this->getName();
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName()
    {
        return $this->view;
    }

    /**
     * 获取视图文件
     * 如果获取不到指定视图，会启用备用视图（如果存在的话）
     *
     * @return string
     */
    public function file()
    {
        if (! file_exists($this->file) && ! empty($this->fallback))
            return $this->getFallbackFile();

        return $this->file;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}