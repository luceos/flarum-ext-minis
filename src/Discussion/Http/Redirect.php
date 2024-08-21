<?php

namespace Luceos\Minis\Discussion\Http;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extend\Middleware;
use Flarum\Extension\Extension;
use Flarum\Http\Middleware\StartSession;
use Illuminate\Contracts\Container\Container;
use Luceos\Minis\Discussion\Http\Middleware\Redirection;

class Redirect implements ExtenderInterface
{
    protected bool $fromSlug = false;

    public function __construct(
        protected readonly string $match,
        protected readonly ?string $to = null,
        protected int $status = 302
    ) {}

    public function fromSlug(): self
    {
        $this->fromSlug = true;

        return $this;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        (new Middleware)->insertAfter(
            StartSession::class,
            new Redirection($this->match, $this->status, $this->fromSlug, $this->to)
        );
    }
}
