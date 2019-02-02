<?php

use Anhskohbo\NoCaptcha\NoCaptcha;

class NoCaptchaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NoCaptcha
     */
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

        $this->assertEquals($simple, $this->captcha->renderJs());
        $this->assertEquals($withLang, $this->captcha->renderJs('vi'));
        $this->assertEquals($withCallback, $this->captcha->renderJs(null, true, 'myOnloadCallback'));
    }

    public function testV3Link()
    {
        $v3captcha = new NoCaptcha('{secret-key}', '{site-key}', 3);

        $this->assertTrue($v3captcha instanceof NoCaptcha);

        $simple = '<script src="https://www.google.com/recaptcha/api.js?render={site-key}" async defer></script>'."\n";
        $withLang = '<script src="https://www.google.com/recaptcha/api.js?render={site-key}&hl=vi" async defer></script>'."\n";
        $withCallback = '<script src="https://www.google.com/recaptcha/api.js?render={site-key}&render=explicit&onload=myOnloadCallback" async defer></script>'."\n";

        $this->assertEquals($simple, $v3captcha->renderJs());
        $this->assertEquals($withLang, $v3captcha->renderJs('vi'));
        $this->assertEquals($withCallback, $v3captcha->renderJs(null, true, 'myOnloadCallback'));
    }

    public function testDisplay()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<div data-sitekey="{site-key}" class="g-recaptcha"></div>';
        $withAttrs = '<div data-theme="light" data-sitekey="{site-key}" class="g-recaptcha"></div>';

        $this->assertEquals($simple, $this->captcha->display());
        $this->assertEquals($withAttrs, $this->captcha->display(['data-theme' => 'light']));
    }

    public function testdisplaySubmit()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $javascript = '<script>function onSubmittest(){document.getElementById("test").submit();}</script>';
        $simple = '<button data-callback="onSubmittest" data-sitekey="{site-key}" class="g-recaptcha"><span>submit</span></button>';
        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmittest" data-sitekey="{site-key}"><span>submit123</span></button>';

        $this->assertEquals($simple . $javascript, $this->captcha->displaySubmit('test'));
        $withAttrsResult = $this->captcha->displaySubmit('test','submit123',['data-theme' => 'light', 'class' => '123']);
        $this->assertEquals($withAttrs . $javascript, $withAttrsResult);
    }

    public function testdisplaySubmitWithCustomCallback()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmitCustomCallback" data-sitekey="{site-key}"><span>submit123</span></button>';

        $withAttrsResult = $this->captcha->displaySubmit('test-custom','submit123',['data-theme' => 'light', 'class' => '123', 'data-callback' => 'onSubmitCustomCallback']);
        $this->assertEquals($withAttrs, $withAttrsResult);
    }
}
