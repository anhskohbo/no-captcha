<?php namespace Anhskohbo\NoCaptcha;

use Illuminate\Support\ServiceProvider;

class NoCaptchaServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$app = $this->app;

		$app['config']->package('anhskohbo/no-captcha', __DIR__.'/config');

		$app['validator']->extend('captcha', function($attribute, $value) use ($app)
		{
			return $app['captcha']->verifyResponse($value, $app['request']->getClientIp());
		});

		if ($app->bound('form'))
		{
			$app['form']->macro('captcha', function($attributes = array()) use ($app)
			{
				return $app['captcha']->display($attributes);
			});
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('captcha', function($app)
		{
			return new NoCaptcha(
				$app['config']->get('no-captcha::secret'),
				$app['config']->get('no-captcha::sitekey'),
				$app['config']->get('no-captcha::lang')
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
