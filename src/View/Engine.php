<?php

namespace TarBlog\View;

/**
 * 之前的模板渲染都是直接混在View类里面的，不太安全，现在单独用一个处理类
 */
class Engine
{
    /**
     * @var View
     */
    protected $view;

    /**
     * Engine constructor.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * 引用视图
     * 可以传递变量哦
     *
     * @param $view
     * @param array $variables
     * @throws ViewNotFoundException
     */
    public function need($view, $variables = [])
    {
        if (!empty($themeDir = $this->view->getThemeDir())) {
            $fileName = str_replace(".","/",$view).'.php';
            if (! file_exists($file = $themeDir . DIRECTORY_SEPARATOR . $fileName))
                throw new ViewNotFoundException("View [".basename($fileName,'.php')."]");
            extract($variables);
            include $file;
        }
    }

    /**
     * 渲染视图
     *
     * @return string|null
     */
    public function render()
    {
        ob_start();

        extract($this->view->getData());

        include $this->view->file();

        $content = ob_get_clean();

        return $content ?: null;
    }
}