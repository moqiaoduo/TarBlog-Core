<?php

namespace TarBlog\Routing;

/**
 * 路由内置匹配表达式
 * 用于where设置时直接使用正则表达式
 */
class RoutePattern
{
    const NUMBER = '^\+?[1-9][0-9]*$';
    const SLUG = '^[A-Za-z0-9-_%]+$';
    const YEAR = '^\d{4,4}$';
    const MONTH = '^\d{1,2}$';
    const DAY = '^\d{1,2}$';
}