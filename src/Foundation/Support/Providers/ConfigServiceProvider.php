<?php

namespace TarBlog\Foundation\Support\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TarBlog\Foundation\Options;

class ConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $configs = [];
        // 加载数据库中的配置，注意这里加载的是user=0即网站配置项，用户的配置在别处加载
        try {
            if (Schema::hasTable('options')) {
                $configs = DB::table('options')->where('user',0)
                    ->get()->pluck('value','name')->toArray();
                Config::set('options',$configs);
            }
        } catch (\Exception $e) {
            // 目前这里还不用处理
        } finally {
            if (empty($configs['timezone'])) $configs['timezone'] = 'Asia/Shanghai';

            date_default_timezone_set($configs['timezone']); // 终于轮到你加载了...
        }

        // 为了让视图安全获取options表数据，故设此类
        $this->app->singleton('options',function ($app) use ($configs) {
            return new Options($app, $configs);
        });
    }
}