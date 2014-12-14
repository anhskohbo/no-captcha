<?php namespace Anhskohbo\NoCaptcha;

class NoCaptcha {

	const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
	const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * //
	 * 
	 * @var string
	 */
	protected $secret;

	/**
	 * //
	 * 
	 * @var string
	 */
	protected $sitekey;

	/**
	 * //
	 * 
	 * @var string
	 */
	protected $lang;

	/**
	 * //
	 * 
	 * @param string $secret
	 * @param string $sitekey
	 */
	public function __construct($secret, $sitekey, $lang = null)
	{
		$this->lang = $lang;

		$this->secret = $secret;

		$this->sitekey = $sitekey;
	}

	/**
	 * //
	 * 
	 * @return string
	 */
	public function display($attributes = array())
	{
		$attributes['data-sitekey'] = $this->sitekey;

		$html  = '<script src="'.$this->getJsLink().'" async defer></script>'."\n";
		$html .= '<div class="g-recaptcha"'.$this->buildAttributes($attributes).'></div>';

		return $html;
	}

	/**
	 * //
	 * 
	 * @param  string $response
	 * @param  string $clientIp
	 * @return bool
	 */
	public function verifyResponse($response, $clientIp = null)
	{
		if (empty($response)) return false;

		$response = $this->sendRequestVerify(array(
			'secret'   => $this->secret,
			'response' => $response,
			'remoteip' => $clientIp
		));

		return isset($response['success']) && $response['success'] === true;
	}

	/**
	 * //
	 * 
	 * @return string
	 */
	public function getJsLink()
	{
		if ($this->lang)
		{
			return static::CLIENT_API.'?hl='.$this->lang;
		}

		return static::CLIENT_API;
	}

	/**
	 * //
	 * 
	 * @param  array  $query
	 * @return array
	 */
	protected function sendRequestVerify(array $query = array())
	{
		$link = static::VERIFY_URL.'?'.http_build_query($query);

		$response = file_get_contents($link);

		return json_decode($response, true);
	}

	/**
	 * //
	 * 
	 * @param  array  $attributes
	 * @return string
	 */
	protected function buildAttributes(array $attributes)
	{
		$html = array();

		foreach ($attributes as $key => $value)
		{
			$html[] = $key.'="'.$value.'"';
		}

		return count($html) ? ' '.implode(' ', $html) : '';
	}

}
