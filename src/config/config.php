<?php

return array(

	'secret'  => getenv('NOCAPTCHA_SECRET') ?: '',
	'sitekey' => getenv('NOCAPTCHA_SITEKEY') ?: '',

	'lang'    => app()->getLocale(),

);
