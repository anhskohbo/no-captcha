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
     * @param array $attributes
     *
     * @return string
     */
    public function display($attributes = [])
    {
        $attributes = $this->prepareAttributes($attributes);

        return '<div' . $this->buildAttributes($attributes) . '></div>';
    }

    /**
     * @see display()
     */
    public function displayWidget($attributes = [])
    {
        return $this->display($attributes);
    }

    /**
     * Display a Invisible reCAPTCHA by embedding a callback into a form submit button.
     *
     * @param string $formIdentifier the html ID of the form that should be submitted.
     * @param string $text the text inside the form button
     * @param array $attributes array of additional html elements
     *
     * @return string
     */
    public function displaySubmit($formIdentifier, $text = 'submit', $attributes = [])
    {
        if (!isset($attributes['data-callback'])) {
            $attributes['data-callback'] = $this->buildCallbackFunctionName($formIdentifier);
        }

        $attributes = $this->prepareAttributes($attributes);

        $button = sprintf('<button%s><span>%s</span></button>', $this->buildAttributes($attributes), $text);

        return $button;
    }

    /**
     * Render js source
     *
     * @param null $lang
     * @param bool $callback
     * @param string $onLoadClass
     *
     * @return string
     */
    public function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack')
    {
        return '<script src="'.$this->getJsLink($lang, $callback, $onLoadClass).'" async defer></script>'."\n";
    }

    /**
     * Render callback js source for `displaySubmit`.
     *
     * @param string $formIdentifier
     *
     * @return string
     */
    public function renderSubmitJs($formIdentifier)
    {
        return sprintf(
            '<script>function %s(){document.getElementById("%s").submit();}</script>',
            $this->buildCallbackFunctionName($formIdentifier),
            $formIdentifier
        );
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
     * Prepare HTML attributes and assure that the correct classes and attributes for captcha are inserted.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }

        $attributes['data-sitekey'] = $this->sitekey;
        $attributes['class'] = trim('g-recaptcha ' . $attributes['class']);

        return $attributes;
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

        if (count($html)) {
            return ' '.implode(' ', $html);
        } else {
            return '';
        }
    }

    /**
     * Build callback function name to use in `data-callback` attribute.
     *
     * @param string $formIdentifier
     *
     * @return string
     */
    protected function buildCallbackFunctionName($formIdentifier)
    {
        return 'onSubmit' . str_replace(['-', '=', '\'', '"', '<', '>', '`'], '', ucfirst($formIdentifier));
    }
}
