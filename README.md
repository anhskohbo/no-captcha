No CAPTCHA reCAPTCHA [![Build Status](https://travis-ci.org/anhskohbo/no-captcha.svg?branch=master&style=flat-square)](https://travis-ci.org/anhskohbo/no-captcha)
==========

![recaptcha_anchor 2x](https://cloud.githubusercontent.com/assets/1529454/5291635/1c426412-7b88-11e4-8d16-46161a081ece.gif)


## Installation

Add the following line to the `require` section of `composer.json`:

```json
{
    "require": {
        "anhskohbo/no-captcha": "*"
    }
}
```

Run `composer update`.

## Laravel

### Setup

Add ServiceProvider to the providers array in `app/config/app.php`.

```
'Anhskohbo\NoCaptcha\NoCaptchaServiceProvider',
```

### Configuration
Run `php artisan config:publish anhskohbo/no-captcha` (`publish:config` if you use Laravel 5).

Fill secret and sitekey config in `app/config/packages/anhskohbo/no-captcha/config.php` file:

```php
<?php

return array(

	'secret'  => '',
	'sitekey' => '',

);
```

### Usage

##### Display reCAPTCHA

Insert reCAPTCHA inside your form using one of this examples:

For Laravel 4
```php
<?php echo Form::captcha() ?>
````

For Laravel 4 using Blade syntax
```php
{{ Form::captcha() }}
```

For Laravel 5
```php
<?php echo app('captcha')->display(); ?>
```

For Laravel 5 using Blade syntax
```php
{!! app('captcha')->display(); !!}
```

For Laravel 5 with `illuminate/html` package using Blade syntax
```php
{!! Form::captcha() !!}
```

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php

$validate = Validator::make(Input::all(), [
	'g-recaptcha-response' => 'required|captcha'
]);

```


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
    <button type="submit">Submit</submit>
</form>

```

## Contribute

https://github.com/anhskohbo/no-captcha/pulls
