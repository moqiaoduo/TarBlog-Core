<?php


namespace TarBlog\Plugin;


class Factory
{
    protected $instances;

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
}