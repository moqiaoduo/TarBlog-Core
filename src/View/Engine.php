<?php

namespace TarBlog\View;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Traits\Macroable;
use TarBlog\Foundation\Options;
use TarBlog\Support\Queue;

/**
 * 之前的模板渲染都是直接混在View类里面的，不太安全，现在单独用一个处理类
 *
 * 以前用 extract 方法导出视图数据，现在用 $this->var 获取视图数据，用 $this->var() 显示视图数据
 *
 * 宏指令的用法，见 README.MD
 *
 * @property string $respondId
 */
class Engine
{
    use Macroable {
        __call as __microCall;
    } // 使用宏指令，方便插入外部方法/函数，供模板调用

    use Queue;

    /**
     * 视图对象
     *
     * @var View
     */
    protected $view;

    /**
     * 标题
     *
     * @var string
     */
    private $archiveTitle = NULL;

    /**
     * 类型
     *
     * @var string
     */
    private $type = 'index';

    /**
     * options表内容
     *
     * @var Options
     */
    private $options;

    /**
     * Engine constructor.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;

        $this->options = app('options');
    }

    /**
     * 渲染视图
     *
     * @return string|null
     * @throws ViewNotFoundException
     */
    public function render()
    {
        ob_start();

        if (! file_exists($this->view->file()))
            throw new ViewNotFoundException("View [{$this->view->name()}] does not exist.");

        // 加载视图文件之前，先检测主题目录是否有 functions.php
        if (file_exists($functionsFile = $this->view->getThemeDir() . DIRECTORY_SEPARATOR . 'functions.php'))
            include_once $functionsFile;

        include $this->view->file();

        $content = ob_get_clean();

        return $content ?: null;
    }

    /**
     * 引用视图
     *
     * @param $view
     * @throws ViewNotFoundException
     */
    public function need($view)
    {
        if (! empty($themeDir = $this->view->getThemeDir())) {
            $fileName = str_replace(".","/",$view) . '.php';
            if (! file_exists($file = $themeDir . DIRECTORY_SEPARATOR . $fileName))
                throw new ViewNotFoundException("View [$view] does not exist.");
            include $file;
        }
    }

    /**
     * 显示页面的标题
     *
     * @param null $defines
     * @param string $before
     * @param string $end
     */
    public function archiveTitle($defines = NULL, $before = ' &raquo; ', $end = '')
    {
        if ($this->archiveTitle) {
            $define = '%s';
            if (is_array($defines) && !empty($defines[$this->type])) {
                $define = $defines[$this->type];
            }

            echo $before . sprintf($define, $this->archiveTitle) . $end;
        }
    }

    /**
     * @return string
     */
    public function getArchiveTitle(): string
    {
        return $this->archiveTitle;
    }

    /**
     * @param string $title
     */
    public function setArchiveTitle(string $title): void
    {
        $this->archiveTitle = $title;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * 判断类型
     *
     * @param $type
     * @return bool
     */
    public function is($type)
    {
        return $type === $this->type;
    }

    public function header()
    {
        $keywords = getOption('keyword');
        $description = getOption('description');
        echo <<<EOF
<meta name="keywords" content="$keywords">
<meta name="generator" content="TarBlog">
<meta name="description" content="$description">
EOF;

//        app('plugin')->trigger('header');
    }

    protected function footer()
    {
        echo <<<EOF
<script type="text/javascript">
(function () {
    window.TarBlogComment = {
        dom : function (id) {
            return document.getElementById(id);
        },
    
        create : function (tag, attr) {
            var el = document.createElement(tag);
        
            for (var key in attr) {
                el.setAttribute(key, attr[key]);
            }
        
            return el;
        },

        reply : function (cid, coid) {
            var comment = this.dom(cid), parent = comment.parentNode,
                response = this.dom('{$this->respondId}'), input = this.dom('comment-parent'),
                form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
                textarea = response.getElementsByTagName('textarea')[0];

            if (null == input) {
                input = this.create('input', {
                    'type' : 'hidden',
                    'name' : 'parent',
                    'id'   : 'comment-parent'
                });

                form.appendChild(input);
            }

            input.setAttribute('value', coid);

            if (null == this.dom('comment-form-place-holder')) {
                var holder = this.create('div', {
                    'id' : 'comment-form-place-holder'
                });

                response.parentNode.insertBefore(holder, response);
            }

            comment.appendChild(response);
            this.dom('cancel-comment-reply-link').style.display = '';

            if (null != textarea && 'text' == textarea.name) {
                textarea.focus();
            }

            return false;
        },

        cancelReply : function () {
            var response = this.dom('{$this->respondId}'),
            holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');

            if (null != input) {
                input.parentNode.removeChild(input);
            }

            if (null == holder) {
                return true;
            }

            this.dom('cancel-comment-reply-link').style.display = 'none';
            holder.parentNode.insertBefore(response, holder);
            return false;
        }
    };
})();
</script>
EOF;

//        app('plugin')->trigger('footer');
    }

    /**
     * 输出cookie记忆别名
     *
     * @access public
     * @param string $cookieName 已经记忆的cookie名称
     * @param boolean $return 是否返回
     * @return string|void
     */
    public static function remember($cookieName, $return = false)
    {
        $cookieName = strtolower($cookieName);

        if (!in_array($cookieName, array('author', 'mail', 'url'))) {
            return '';
        }

        $value = Cookie::get('__tarblog_remember_' . $cookieName);

        if ($return) {
            return $value;
        } else {
            echo htmlspecialchars($value);
        }
    }

    /**
     * 魔术方法，用于获取视图数据
     *
     * @param $name
     * @return mixed|void
     */
    public function __get($name)
    {
        if (isset($this->view[$name]))
            return $this->view[$name];
    }

    /**
     * 魔术方法，用于设置视图数据
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->view[$name] = $value;
    }

    /**
     * 魔术方法，用于执行宏或显示视图数据
     *
     * @param $method
     * @param $parameters
     * @return mixed|void
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->__microCall($method, $parameters);
        } catch (\BadMethodCallException $e) {
            // 不是宏指令的话 就是队列数据或视图数据
            if (isset($this->row[$method]))
                echo $this->row[$method];
            elseif (isset($this->view[$method]))
                echo $this->view[$method];
        }
    }
}