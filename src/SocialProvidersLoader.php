<?php

namespace MadWeb\SocialAuth;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;

class SocialProvidersLoader
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheKey = 'mad-web.social-providers';

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $social_model;

    /**
     * SocialProvidersLoader constructor.
     *
     * @param  Repository  $cache
     * @param  Dispatcher  $dispatcher
     */
    public function __construct(Repository $cache, Dispatcher $dispatcher)
    {
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;

        $this->social_model = config('social-auth.models.social');
    }

    /**
     * Get available social providers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSocialProviders()
    {
        return $this->cache->rememberForever($this->cacheKey, function () {
            $model = $this->social_model;

            return $model::all();
        });
    }

    /**
     * Forget cached social providers.
     */
    public function forgetSocialProviders()
    {
        $this->cache->forget($this->cacheKey);
    }

    /**
     * Register additional social providers.
     */
    public function registerSocialProviders()
    {
        $this->registerCacheRefresher();

        foreach (config('social-auth.providers') as $provider) {
            $this->dispatcher->listen(
                \SocialiteProviders\Manager\SocialiteWasCalled::class,
                $provider.'@handle'
            );
        }
    }

    /**
     * Remove cache data on social providers table update.
     */
    protected function registerCacheRefresher()
    {
        $model = $this->social_model;

        $ClearCacheFunction = function () {
            $this->forgetSocialProviders();
        };

        $model::created($ClearCacheFunction);
        $model::updated($ClearCacheFunction);
        $model::deleted($ClearCacheFunction);
    }
}
