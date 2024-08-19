<?php

namespace Luceos\Minis\User\Email;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extend\Validator as Extend;
use Flarum\Extension\Extension;
use Flarum\User\UserValidator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class RequireDomain implements ExtenderInterface
{
    protected readonly array $domains;

    public function __construct(...$domains)
    {
        $this->domains = $domains;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        (new Extend(UserValidator::class))
            ->configure(function (UserValidator $user, Validator $validator) {
                $rules = $validator->getRules()['email'];

                $domains = collect($this->domains)
                    ->map(fn (string $domain) => Str::startsWith($domain, '@') ? $domain : "@$domain")
                    ->toArray();

                $rules[] = 'ends_with:' . implode(',', $domains);

                $validator->addRules(['email' => $rules]);
            });
    }
}
