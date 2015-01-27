<?php namespace Anhskohbo\NoCaptcha\Facades;

use Illuminate\Support\Facade\Facade;

class NoCaptcha extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'captcha'; }

}
