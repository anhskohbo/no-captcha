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

    public function testDisplay()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple ='<script src="https://www.google.com/recaptcha/api.js" async defer></script>'."\n".
            '<div class="g-recaptcha" data-sitekey="{site-key}"></div>';

        $withLang ='<script src="https://www.google.com/recaptcha/api.js?hl=vi" async defer></script>'."\n".
            '<div class="g-recaptcha" data-sitekey="{site-key}"></div>';

        $withAttrs ='<script src="https://www.google.com/recaptcha/api.js" async defer></script>'."\n".
            '<div class="g-recaptcha" data-theme="light" data-sitekey="{site-key}"></div>';

        $this->assertEquals($this->captcha->display(), $simple);
        $this->assertEquals($this->captcha->display([], 'vi'), $withLang);
        $this->assertEquals($this->captcha->display(['data-theme' => 'light']), $withAttrs);
    }

    public function testDisplayScript()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'."\n";

        $withLang ='<script src="https://www.google.com/recaptcha/api.js?hl=vi" async defer></script>'."\n";

        $this->assertEquals($this->captcha->displayScript(), $simple);
        $this->assertEquals($this->captcha->displayScript('vi'), $withLang);
    }

    public function testDisplayField()
    {
        $this->assertTrue($this->captcha instanceof NoCaptcha);

        $simple = '<div class="g-recaptcha" data-sitekey="{site-key}"></div>';

        $withAttrs ='<div class="g-recaptcha" data-theme="light" data-sitekey="{site-key}"></div>';

        $this->assertEquals($this->captcha->displayCaptchaField(), $simple);
        $this->assertEquals($this->captcha->displayCaptchaField(['data-theme' => 'light']), $withAttrs);
    }
}
