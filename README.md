No CAPTCHA reCAPTCHA [![Build Status](https://travis-ci.org/anhskohbo/no-captcha.svg?branch=master&style=flat-square)](https://travis-ci.org/anhskohbo/no-captcha)
==========

![recaptcha_anchor 2x](https://cloud.githubusercontent.com/assets/1529454/5291635/1c426412-7b88-11e4-8d16-46161a081ece.gif)

> For Laravel 4 use [v1](https://github.com/anhskohbo/no-captcha/tree/v1) branch.

## Installation

```
composer require anhskohbo/no-captcha
```

## Laravel 5

### Setup

Add ServiceProvider to the providers array in `app/config/app.php`.

```
Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class,
```

### Configuration

Add `NOCAPTCHA_SECRET` and `NOCAPTCHA_SITEKEY` in **.env** file (without brackets):

```
NOCAPTCHA_SECRET=secret-key
NOCAPTCHA_SITEKEY=site-key
```

### Usage

##### Display reCAPTCHA

```php
{!! app('captcha')->display(); !!}
```

With custom attributes and language support:

```
{!! app('captcha')->display($attributes = [], $lang = null); !!}
```

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php

$validate = Validator::make(Input::all(), [
	'g-recaptcha-response' => 'required|captcha'
]);

```

### Testing

When using the [Laravel Testing functionality](http://laravel.com/docs/5.1/testing), you will need to mock out the response for the captcha form element. To do this:

1) Setup NoCaptcha facade in config/app.conf

```php
'NoCaptcha' => 'Anhskohbo\NoCaptcha\Facades\NoCaptcha'
```

2) For any form tests involving the captcha, you can then mock the facade behaviour:

```php
// prevent validation error on captcha
NoCaptcha::shouldReceive('verifyResponse')
    ->once()
    ->andReturn(true);
// provide hidden input for your 'required' validation
NoCaptcha::shouldReceive('display')
    ->zeroOrMoreTimes()
    ->andReturn('<input type="hidden" name="g-recaptcha-response" value="1" />');
```

You can then test the remainder of your form as normal.

## Without Laravel

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$secret  = '';
$sitekey = '';
$captcha = new \Anhskohbo\NoCaptcha\NoCaptcha($secret, $sitekey);

if ( ! empty($_POST)) {
    var_dump($captcha->verifyResponse($_POST['g-recaptcha-response']));
    exit();
}

?>

<form action="?" method="POST">
    <?php echo $captcha->display(); ?>
    <button type="submit">Submit</button>
</form>

```

## Contribute

https://github.com/anhskohbo/no-captcha/pulls
