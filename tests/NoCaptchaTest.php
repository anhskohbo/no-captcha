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

        $this->assertEquals($this->captcha->renderJs(), $simple);
        $this->assertEquals($this->captcha->renderJs('vi'), $withLang);
        $this->assertEquals($this->captcha->renderJs(null, true, 'myOnloadCallback'), $withCallback);
    }

    public function testDisplay()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<div data-sitekey="{site-key}" class="g-recaptcha"></div>';
        $withAttrs = '<div data-theme="light" data-sitekey="{site-key}" class="g-recaptcha"></div>';

        $this->assertEquals($this->captcha->display(), $simple);
        $this->assertEquals($this->captcha->display(['data-theme' => 'light']), $withAttrs);
    }

    public function testdisplaySubmit()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $javascript = '<script>function onSubmittest(){document.getElementById("test").submit();}</script>';
        $simple = '<button data-callback="onSubmittest" data-sitekey="{site-key}" class="g-recaptcha"><span>submit</span></button>';
        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmittest" data-sitekey="{site-key}"><span>submit123</span></button>';

        $this->assertEquals($this->captcha->displaySubmit('test'), $simple . $javascript);
        $withAttrsResult = $this->captcha->displaySubmit('test','submit123',['data-theme' => 'light', 'class' => '123']);
        $this->assertEquals($withAttrsResult, $withAttrs . $javascript);
    }
}
