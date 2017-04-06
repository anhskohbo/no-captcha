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
     * API requested control
     */
    protected $api_requested = 0;

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
        $this->http = new Client([
            'timeout'  => 2.0,
        ]);
    }

    /**
     * Render HTML captcha.
     *
     * @return string
     */
    public function display($attributes = [], $lang = null)
    {
        if ($this->api_requested !== 0) {
            return 'To request multiple instances of NoCaptcha on the same page you must use the function multiple_display().';
        }
        
        $this->api_requested = true;
        
        $attributes['data-sitekey'] = $this->sitekey;
        
        $html = '<script src="'.$this->getJsLink($lang).'" async defer></script>'."\n";
        $html .= '<div class="g-recaptcha"'.$this->buildAttributes($attributes).'></div>';
        
        return $html;
    }
    
    /**
     * Render multiple HTML captcha on the same page ( Explicitly render the reCAPTCHA widget ).
     *
     * @return string
     */
    public function multiple_display($attributes = [], $lang = null)
    {
        if ($this->api_requested === true) {
            return 'You cannot use display() and multiple_display() from NoCaptcha on the same page.';
        }
        
        $this->api_requested += 1;
        
        $attributes['data-sitekey'] = $this->sitekey;
        $attributes['id'] = 'grecaptcha'.$this->api_requested;
        
        $html = '';
        if ($this->api_requested === 1) {
            $html .= '<script>function NoCaptchaCallback() { var elems = document.getElementsByClassName("g-recaptcha"); for (i=0; i<elems.length; i++) { grecaptcha.render(elems[i].id, {"sitekey" : "'.$this->sitekey.'"}); } }</script>'."\n";
            $html .= '<script src="'.$this->getJsLink($lang).'?onload=NoCaptchaCallback&render=explicit" async defer></script>'."\n";
        }
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
    public function getJsLink($lang = null)
    {
        return $lang ? static::CLIENT_API.'?hl='.$lang : static::CLIENT_API;
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
