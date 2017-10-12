<?php

use Anhskohbo\NoCaptcha\NoCaptcha;

class NoCaptchaTest extends PHPUnit_Framework_TestCase
{
    private $captcha;

    public function setUp()
    {
        parent::setUp();
        $this->captcha = new NoCaptcha('{secret-key}', '{site-key}');
    }

    public function testJsLink()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<script src="https://www.google.com/recaptcha/api.js?" async defer></script>'."\n";
        $withLang = '<script src="https://www.google.com/recaptcha/api.js?hl=vi" async defer></script>'."\n";
        $withCallback = '<script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=myOnloadCallback" async defer></script>'."\n";

        $this->assertEquals($this->captcha->renderJs(), $simple);
        $this->assertEquals($this->captcha->renderJs('vi'), $withLang);
        $this->assertEquals($this->captcha->renderJs(null, true, 'myOnloadCallback'), $withCallback);
    }

    public function testDisplay()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<div class="g-recaptcha" data-sitekey="{site-key}"></div>';
        $withAttrs = '<div class="g-recaptcha" data-theme="light" data-sitekey="{site-key}"></div>';

        $this->assertEquals($this->captcha->display(), $simple);
        $this->assertEquals($this->captcha->display(['data-theme' => 'light']), $withAttrs);
    }
}
