<?php

namespace Anhskohbo\NoCaptcha;

use Illuminate\Support\ServiceProvider;

class NoCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $app = $this->app;

        /**
         * no use on terminal, at least for now
         */
        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            return;
        }

        $this->bootConfig();

        $app['validator']->extend('captcha', function ($attribute, $value) use ($app) {
            return $app['captcha']->verifyResponse($value, $app['request']->getClientIp());
        });

        if ($app->bound('form')) {
            $app['form']->macro('captcha', function ($attributes = []) use ($app) {
                return $app['captcha']->display($attributes, $app->getLocale());
            });
        }
    }

    /**
     * Booting configure.
     */
    protected function bootConfig()
    {
        $path = __DIR__.'/config/captcha.php';

        $this->mergeConfigFrom($path, 'captcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('captcha.php')]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        /**
         * no use on terminal, at least for now
         */
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            return;
        }
        
        $this->app->singleton('captcha', function ($app) {
            return new NoCaptcha(
                $app['config']['captcha.secret'],
                $app['config']['captcha.sitekey'],
                $app['config']['captcha.options']
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['captcha'];
    }
}
