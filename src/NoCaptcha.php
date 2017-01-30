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
     * API requested control
     */
    protected $api_requested = false;

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

    /**
     * Render HTML captcha.
     *
     * @param array $attributes
     * @param string $lang
     *
     * @return string
     */
    public function display($attributes = [], $lang = null)
    {
        $attributes['data-sitekey'] = $this->sitekey;

        $html = '';
        if (empty($this->api_requested)) {
            $this->api_requested = true;
            $html .= '<script src="'.$this->getJsLink($lang).'" async defer></script>'."\n";
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
     * @param string $lang
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
        $context = $this->prepareContext($query);

        return json_decode(
            file_get_contents(static::VERIFY_URL, false, $context), true
        );
    }

    /**
     * Preparing data to send.
     *
     * @param array $query
     * @return resource
     */
    public function prepareContext(array $query = [])
    {
        $postdata = http_build_query(
            $query
        );

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];

        return stream_context_create($opts);
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
