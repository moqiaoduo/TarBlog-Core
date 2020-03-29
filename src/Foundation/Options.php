<?php

namespace TarBlog\Foundation;

use Illuminate\Support\Facades\DB;

class Options
{
    /**
     * 当前应用
     *
     * @var Application
     */
    protected $app;

    /**
     * 配置数据
     *
     * @var array
     */
    protected $data;

    /**
     * 用户数据集
     * 读取后缓存到这里
     *
     * @var array
     */
    protected $user_data = [];

    public function __construct($app, $data = [])
    {
        $this->app = $app;

        $this->data = $data;
    }

    public function __call($name, $arguments)
    {
        if (isset($this->data[$name]))
            echo $this->data[$name];
    }

    /**
     * 魔术方法，用于获取数据
     * 仅支持非用户数据
     * 用户数据请用user方法读取
     *
     * @param $name
     * @return mixed|void
     */
    public function __get($name)
    {
        if (isset($this->data[$name]))
            return $this->data[$name];
    }

    public function user($key, $user, $default = null)
    {
        if (isset($this->user_data[$user][$key]))
            return $this->user_data[$user][$key];

        $data = DB::table('options')->where('name',$key)->where('user',$user)->first();

        if ($data == null) return $default;

        return $data->value;
    }

    /**
     * 将设置写入数据库
     *
     * @param $key
     * @param $value
     * @param $user
     */
    public function writeToDB($key, $value, $user)
    {
        DB::table('options')->updateOrInsert(['name'=>$key,'user'=>$user],['value'=>$value]);

        if ($user = 0)
            $this->data[$key] = $value; // 更新到数据集中
        else
            $this->user_data[$user][$key] = $value;
    }

    public function title()
    {
        echo $this->data['siteName'];
    }

    public function siteUrl($ext = '')
    {
        echo $this->data['siteName'] . (substr($ext,0,1) == '/' ? $ext : '/' . $ext);
    }

    /**
     * 输出后台路径
     * 应用程序里面必须定义admin路由，否则就会菠萝菠萝哒（指报错）
     */
    public function adminUrl()
    {
        echo route('admin');
    }

    /**
     * 获取个人档案地址（后台）
     * 应用程序里面必须定义admin.profile路由，否则就会菠萝菠萝哒（指报错）
     */
    public function profileUrl()
    {
        echo route('admin.profile');
    }

    /**
     * 登录URL
     * 全套Auth::routes()搞起来秋梨膏
     */
    public function loginUrl()
    {
        $this->siteUrl("login.php");
    }

    /**
     * 登出URL
     * 全套Auth::routes()搞起来秋梨膏
     */
    public function logoutUrl()
    {
        echo route('logout');
    }

    /**
     * 注册URL
     * 全套Auth::routes()搞起来秋梨膏
     */
    public function registerUrl()
    {
        echo route('register');
    }
}