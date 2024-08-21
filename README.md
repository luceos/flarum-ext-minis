# Minis

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/luceos/minis.svg)](https://packagist.org/packages/luceos/minis) [![Total Downloads](https://img.shields.io/packagist/dt/luceos/minis.svg)](https://packagist.org/packages/luceos/minis)

A [Flarum](http://flarum.org) extension that is not really an extension. 

Every Flarum installation comes with a file `extend.php` which allows you use any [Flarum extender](https://docs.flarum.org/extenders/) without creating a full-fledged extension. For the past several years I have been writing [several examples](https://github.com/luceos/flarum-extender-examples) to show people how to use this. I thought it wise to move some of these into their own extension so that you can use these extenders with less code.

Your `extend.php` lives in the root installation path of Flarum, next to `config.php` and `flarum`.

## Installation

Install with composer:

```sh
composer require luceos/minis:"*"
```

## Updating

```sh
composer update luceos/minis:"*"
```

## Extenders

### How to use

> There's no need to enable this extension. Everything from this extension works "as is", as long as it's installed with composer.

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

### Discussion - Http

`Luceos\Minis\Discussion\Http\Redirection` - allows redirecting requests to certain discussions

_Redirect any request to a path with words, dashes or numbers to be matched against a Discussion and redirected to it._
```php
  (new Luceos\Minis\Discussion\Http\Redirect(match: '~(?<slug>[\w\d-]{4,})~'))->fromSlug(),
```

**Note `fromSlug()` requires a capture group named `slug` in your regular expression!**

_Redirect any request to a matched path to a specific path where we replace matches.
```php
  (new Luceos\Minis\Discussion\Http\Redirect(match: '~wordpress\/([0-9]+)\/(.*)~', to: '$1/$2', status: 301)),
```

- `match` requires a valid regular expression, check an online regular expression tester on how to use regular expressions: https://regex101.com/.
- `to` optional argument to set a target for, you can use `$1` for the first matched capture group or use named capture groups as well

## Links

- [Packagist](https://packagist.org/packages/luceos/minis)
- [GitHub](https://github.com/luceos/flarum-ext-minis)
- [Discuss](https://discuss.flarum.org/d/35283)
