<?php

namespace Anhskohbo\NoCaptcha;

use Symfony\Component\HttpFoundation\Request;

class NoCaptcha
{
  const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
  const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

  /**
  * The recaptcha secret key.
  *
  * @var string
  */
  protected $secret;

  /**
  * The recaptcha sitekey key.
  *
  * @var string
  */
  protected $sitekey;

  /**
  * NoCaptcha.
  *
  * @param string $secret
  * @param string $sitekey
  */
  public function __construct($secret, $sitekey)
  {
    $this->secret = $secret;
    $this->sitekey = $sitekey;
  }

  // public static function setClientApiLink = ($link) {
  //   $this->
  // }

  /**
  * Render HTML captcha.
  *
  * @return string
  */
  public function display($attributes = [], $lang = null, $apiParams = [])
  {
    $attributes['data-sitekey'] = $this->sitekey;

    // Fix for older format still in use
    if (isset($lang)) {
      $apiParams[] = ['h1'=>$lang];
    }

    $html = '<script src="'.$this->getJsLink($apiParams).'" async defer></script>'."\n";
    $html .= '<div class="g-recaptcha"'.$this->buildAttributes($attributes).'></div>';

    return $html;
  }

  /**
  * Verify no-captcha response.
  *
  * @param string $response
  * @param string $clientIp
  *
  * @return bool
  */
  public function verifyResponse($response, $clientIp = null)
  {
    if (empty($response)) {
      return false;
    }

    $response = $this->sendRequestVerify([
      'secret' => $this->secret,
      'response' => $response,
      'remoteip' => $clientIp,
    ]);

    return isset($response['success']) && $response['success'] === true;
  }

  /**
  * Verify no-captcha response by Symfony Request.
  *
  * @param Request $request
  *
  * @return bool
  */
  public function verifyRequest(Request $request)
  {
    return $this->verifyResponse(
    $request->get('g-recaptcha-response'),
    $request->getClientIp()
  );
}

/**
* Get recaptcha js link.
*
* @return string
*/
public function getJsLink($params = [])
{
  //generated params
  $gParams = '';
  // Generate params if they exist
  if ( count($params) > 0) {
    $keys = array_keys($params);
    foreach ($params as $key => $val) {
      // First param begins with ? every other after is &
      $format = ($params[$keys[0]] == $val) ? '?' : '&';
      $gParams .= $format.$key.'='.$val;
    }
  }

  return static::CLIENT_API.$gParams;
}

/**
* Send verify request.
*
* @param array $query
*
* @return array
*/
protected function sendRequestVerify(array $query = [])
{
  // This taken from: https://github.com/google/recaptcha/blob/master/src/ReCaptcha/RequestMethod/Post.php
  $peer_key = version_compare(PHP_VERSION, '5.6.0', '<') ? 'CN_name' : 'peer_name';

  $context = stream_context_create(array(
    'http' => array(
      'header' => "Content-type: application/x-www-form-urlencoded\r\n",
      'method' => 'POST',
      'content' => http_build_query($query, '', '&'),
      'verify_peer' => true,
      $peer_key => 'www.google.com',
    ),
  ));

  $response = file_get_contents(static::VERIFY_URL, false, $context);

  return json_decode($response, true);
}

/**
* Build HTML attributes.
*
* @param array $attributes
*
* @return string
*/
protected function buildAttributes(array $attributes)
{
  $html = [];

  foreach ($attributes as $key => $value) {
    $html[] = $key.'="'.$value.'"';
  }

  return count($html) ? ' '.implode(' ', $html) : '';
}
}
