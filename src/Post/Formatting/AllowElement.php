<?php

namespace Luceos\Minis\Post\Formatting;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extend\Formatter;
use Flarum\Extension\Extension;
use Illuminate\Contracts\Container\Container;
use s9e\TextFormatter\Configurator;

class AllowElement implements ExtenderInterface
{
    public function __construct(protected string $element)
    {
    }

    public function extend(Container $container, Extension $extension = null)
    {
        (new Formatter)
            ->configure(function (Configurator $configurator) {
                $configurator->HTMLElements->allowUnsafeElement($this->element);
            });
    }
}
