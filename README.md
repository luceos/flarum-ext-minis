# Minis

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/luceos/minis.svg)](https://packagist.org/packages/luceos/minis) [![Total Downloads](https://img.shields.io/packagist/dt/luceos/minis.svg)](https://packagist.org/packages/luceos/minis)

A [Flarum](http://flarum.org) extension. Offers many little extenders to be used inside your `extend.php` in the root of your Flarum installation path (next to config.php and flarum files).

## Installation

Install with composer:

```sh
composer require luceos/minis:"*"
```

## Updating

```sh
composer update luceos/minis:"*"
php flarum migrate
php flarum cache:clear
```

## Extenders

### How to use

Inside your `extend.php` in the Flarum installation path you will see some lines, at the bottom you will find something like:

```php
return [

];
```

Between the `[` and `]` you can add the follow extender snippets. I've given examples for each of them.

### User - Email

`Luceos\Minis\User\Email\RequireDomain` - limits the email domains that can sign up.

```php
  new Luceos\Minis\User\Email\RequireDomain('@flarum.org', '@gmail.com'),
```

### Post - Throttle

`Luceos\Minis\Post\Throttle\InTag` - throttles how fast a user can reply or create discussions in a certain tag

_Allow replying and creating discussions only once every five minutes in the tag with slug `advertisements`._
```php
  (new Luceos\Minis\Post\Throttle\InTag('advertisements'))
    ->op(true)
    ->reply(true)
    ->interval(fn (\Carbon\Carbon $carbon, \Flarum\User\User $user) => $carbon->subMinutes(5)),
```

Check the Carbon documentation in how to use the interval settings with carbon: https://carbon.nesbot.com/docs/#api-addsub.

_Allow creating discussions only once every day in the tag with slug `advertisements`._
```php
  (new Luceos\Minis\Post\Throttle\InTag('advertisements'))
    ->op(true)
    ->interval(fn (\Carbon\Carbon $carbon, \Flarum\User\User $user) => $carbon->subDay()),
```

### Post - Formatting

`Luceos\Minis\Post\Formatting\AllowElement` - allows using a html element in the editor/composer

```php
  new Luceos\Minis\Post\Formatting\AllowElement('iframe'),
```

## Links

- [Packagist](https://packagist.org/packages/luceos/minis)
- [GitHub](https://github.com/luceos/flarum-ext-minis)
- [Discuss](https://discuss.flarum.org/d/35283)
