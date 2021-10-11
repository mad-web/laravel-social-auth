<?php

namespace MadWeb\SocialAuth;

use MadWeb\SocialAuth\Console\AddSocialProviderCommand;
use MadWeb\SocialAuth\Console\CacheRefreshCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SocialAuthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('social-auth')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('routes')
            ->hasMigration('create_social_providers_table')
            ->hasCommands(CacheRefreshCommand::class, AddSocialProviderCommand::class);
    }

    public function boot()
    {
        parent::boot();

        $loader = $this->app->make(SocialProvidersLoader::class);

        // Share social Providers for views
        view()->composer(['social-auth::buttons', 'social-auth::attach'], function ($view) use ($loader) {
            /* @var \Illuminate\View\View $view */
            $view->with('socialProviders', $loader->getSocialProviders());
        });

        $loader->registerSocialProviders();

        $this->app->register(\SocialiteProviders\Manager\ServiceProvider::class);
    }
}
