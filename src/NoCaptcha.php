<?php

namespace Anhskohbo\NoCaptcha;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

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
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * The cached verified responses.
     *
     * @var array
     */
    protected $verifiedResponses = [];

    /**
     * NoCaptcha.
     *
     * @param string $secret
     * @param string $sitekey
     * @param array $options
     */
    public function __construct($secret, $sitekey, $options = [])
    {
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->http = new Client($options);
    }

    /**
     * Render HTML captcha.
     *
     * @param array  $attributes
     *
     * @return string
     */
    public function display($attributes = [])
    {
        $attributes['data-sitekey'] = $this->sitekey;
        return '<div class="g-recaptcha"'.$this->buildAttributes($attributes).'></div>';
    }

    /**
     * Render js source
     *
     * @param null $lang
     * @param bool $callback
     * @param string $onLoadClass
     * @return string
     */
    public function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack')
    {
        return '<script src="'.$this->getJsLink($lang, $callback, $onLoadClass).'" async defer></script>'."\n";
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

        // Return true if response already verfied before.
        if (in_array($response, $this->verifiedResponses)) {
            return true;
        }

        $verifyResponse = $this->sendRequestVerify([
            'secret' => $this->secret,
            'response' => $response,
            'remoteip' => $clientIp,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            // A response can only be verified once from google, so we need to
            // cache it to make it work in case we want to verify it multiple times.
            $this->verifiedResponses[] = $response;
            return true;
        } else {
            return false;
        }
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
     * @param string $lang
     * @param boolean $callback
     * @param string $onLoadClass
     * @return string
     */
    public function getJsLink($lang = null, $callback = false, $onLoadClass = 'onloadCallBack')
    {
        $client_api = static::CLIENT_API;
        $params = [];

        $callback ? $this->setCallBackParams($params, $onLoadClass)  : false;
        $lang ? $params['hl'] = $lang : null;

        return $client_api . '?'. http_build_query($params);
    }

    /**
     * @param $params
     * @param $onLoadClass
     */
    protected function setCallBackParams(&$params, $onLoadClass)
    {
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
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
        $response = $this->http->request('POST', static::VERIFY_URL, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
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
