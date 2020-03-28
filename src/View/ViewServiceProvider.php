<?php

namespace TarBlog\View;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory();
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->singleton('view', function ($app) {

            $factory = $this->createFactory($app['events']);

            $factory->share('app', $app);

            $factory->setTheme(config('theme','default'));

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \TarBlog\View\Factory
     */
    protected function createFactory($events)
    {
        return new Factory($events);
    }
}
