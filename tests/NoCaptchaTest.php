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

    public function testRequestShouldWorks()
    {
        $response = $this->captcha->verifyResponse('should_false');
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

    public function testSubmitJs()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $javascript = '<script>function onSubmitTest(){document.getElementById("test").submit();}</script>';

        $this->assertEquals($javascript, $this->captcha->renderSubmitJs('test'));
    }

    public function testDisplay()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<div class="g-recaptcha" data-sitekey="{site-key}"></div>';
        $withAttrs = '<div data-theme="light" class="g-recaptcha" data-sitekey="{site-key}"></div>';

        $this->assertEquals($simple, $this->captcha->display());
        $this->assertEquals($withAttrs, $this->captcha->display(['data-theme' => 'light']));
    }

    public function testDisplaySubmit()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<button data-callback="onSubmitTest" class="g-recaptcha" data-sitekey="{site-key}"><span>submit</span></button>';
        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmitTest" data-sitekey="{site-key}"><span>submit123</span></button>';

        $this->assertEquals($simple, $this->captcha->displaySubmit('test'));
        $withAttrsResult = $this->captcha->displaySubmit('test', 'submit123', ['data-theme' => 'light', 'class' => '123']);
        $this->assertEquals($withAttrs, $withAttrsResult);
    }

    public function testDisplaySubmitWithCustomCallback()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmitCustomCallback" data-sitekey="{site-key}"><span>submit123</span></button>';

        $withAttrsResult = $this->captcha->displaySubmit('test-custom', 'submit123', ['data-theme' => 'light', 'class' => '123', 'data-callback' => 'onSubmitCustomCallback']);
        $this->assertEquals($withAttrs, $withAttrsResult);
    }
}
