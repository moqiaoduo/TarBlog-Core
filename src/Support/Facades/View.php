<?php

namespace TarBlog\Support\Facades;

use Illuminate\Support\Facades\Facade;
use TarBlog\View\Engine;

/**
 * @method static bool exists(string $view)
 * @method static \Illuminate\Contracts\View\View file(string $view, string $path = null, bool $notThrow = false)
 * @method static \Illuminate\Contracts\View\View make(string $view, array $data = [], array $mergeData = [])
 * @method static mixed share(array|string $key, $value = null)
 * @method static \Illuminate\Contracts\View\Factory addNamespace(string $namespace, string|array $hints)
 * @method static \Illuminate\Contracts\View\Factory replaceNamespace(string $namespace, string|array $hints)
 *
 * @see \TarBlog\View\Factory
 */
class View extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }

    /**
     * 添加宏指令，用于模板调用
     *
     * @param $name
     * @param mixed|null $macro
     * @throws
     */
    public static function addMacro($name, $macro = null)
    {
        if (is_null($macro)) {
            Engine::mixin($name);
        } else {
            Engine::macro($name, $macro);
        }
    }
}
