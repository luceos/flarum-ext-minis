<?php

namespace Luceos\Minis\Post\Throttle;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Extend\ExtenderInterface;
use Flarum\Extend\ThrottleApi;
use Flarum\Extension\Extension;
use Flarum\Post\CommentPost;
use Flarum\Tags\Tag;
use Flarum\User\User;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class InTag implements ExtenderInterface
{
    protected readonly int|null $tag;

    protected bool $onOp = false;
    protected bool $onReply = false;
    protected Carbon|null $carbon = null;

    public function __construct(string $slug)
    {
        $this->tag = Tag::query()->where('slug', $slug)->value('id');
    }

    public function op(bool $onOp = true): self
    {
        $this->onOp = $onOp;

        return $this;
    }

    public function reply(bool $onReply = true): self
    {
        $this->onReply = $onReply;

        return $this;
    }

    public function interval(callable $carbon): self
    {
        $this->carbon = $carbon;

        return $this;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        if (! $this->tag) return;

        if (! $this->onOp && ! $this->onReply) return;

        if (! $this->carbon) return;

        (new ThrottleApi)
            ->set("luceos-minis-post-throttle-in-tag-$this->tag", function (ServerRequestInterface $request) {

                // Get the route name being requested.
                $routeName = $request->getAttribute('routeName');

                $needsThrottling = ($routeName === 'discussions.create' && $this->onOp)
                    || ($routeName === 'posts.update' && $this->onReply);

                if (! $needsThrottling) return;

                // The current user.
                /** @var User $actor */
                $actor = $request->getAttribute('actor');

                // Restrict the number of discussions to one in this time frame.
                // Eg once per minute: $after = Carbon::now()->subMinute();
                $after = $this->carbon(new Carbon, $actor);

                // Get the posted raw data.
                $data = Arr::get($request->getParsedBody(), 'data', []);

                // Get the discussion creation payload relating to the tags.
                $tags = Arr::get($data, 'relationships.tags.data', []);

                // Reduce creation payload to only tag Ids
                $tags = array_map(fn ($tag) => $tag['id'], $tags);

                // Ignore discussions created without the tag in need of throttling.
                if (! in_array($this->tag, $tags)) return;

                $model = $routeName === 'discussions.create'
                    ? Discussion::class
                    : CommentPost::class;
                $tagRelation = $routeName === 'discussions.create'
                    ? 'tags'
                    : 'discussion.tags';

                // The function needs to return true if we want to throttle and false if not
                // We will load the discussions by this user and if there's at least one
                // we will throttle the user.
                return $model::query()
                    // remove any global scopes that might restrict the query, eg approvals
                    ->withoutGlobalScopes()
                    // which are created in the specified tag
                    ->whereRelation($tagRelation, 'id', '=', $this->tag)
                    // which are by this user
                    ->where('user_id', $actor->id)
                    // created in the time frame since $after
                    ->where('created_at', '>=', $after)
                    // if there are more than 0, we'll return true to throttle, or null to ignore this throttler
                    ->count() > 0 ? true : null;
            });
    }
}
