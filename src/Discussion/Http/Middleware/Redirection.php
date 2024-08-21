<?php

namespace Luceos\Minis\Discussion\Http\Middleware;

use Flarum\Discussion\Discussion;
use Flarum\Http\UrlGenerator;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Redirection implements MiddlewareInterface
{
    public function __construct(
        protected readonly string $match,
        protected readonly int $status,
        protected readonly bool $fromSlug,
        protected readonly ?string $to = null,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (preg_match(
            $this->match,
            $request->getUri()->getPath(),
            $matches
        )) {
            $redirect = match(true) {
                $this->fromSlug && isset($matches['slug']) => $this->discussionFromSlug($matches['slug']),
                $this->to !== null => $this->replaceToWithMatches($this->to, $matches),
                default => $handler->handle($request)
            };

            return new RedirectResponse(
                str_starts_with($redirect, '/') ? $request->getUri()->withPath($redirect) : $redirect,
                $this->status
            );
        }
    }

    protected function discussionFromSlug(string $slug): string
    {
        /** @var Discussion|null $discussion */
        $discussion = Discussion::query()->where('slug', $slug)->first();

        $uri = resolve(UrlGenerator::class);

        return $uri->to('forum')->route(
            'discussion.show',
            ['discussion' => $discussion->id]
        );
    }

    protected function replaceToWithMatches(?string $to, array $matches): string
    {
        return str_replace(
            array_map(array_keys($matches), fn ($key) => "\$$key"),
            array_values($matches),
            $to
        );
    }
}
