<?php

namespace TarBlog\Foundation\Support\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 加载数据库中的配置，注意这里加载的是user=0即网站配置项，用户的配置在别处加载
        try {
            if (Schema::hasTable('options')) {
                Config::set('options',DB::table('options')->where('user',0)
                    ->get()->pluck('value','name')->toArray());
            }
        } catch (\Exception $e) {
            // 目前这里还不用处理
        } finally {
            if ($timezone = config('timezone', 'Asia/Shanghai'))
                date_default_timezone_set($timezone); // 终于轮到你加载了...
        }
    }
}