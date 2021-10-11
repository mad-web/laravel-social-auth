<?php

namespace MadWeb\SocialAuth\Console;

use Illuminate\Console\Command;
use MadWeb\SocialAuth\SocialProvidersLoader;

class CacheRefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social-auth:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh social auth providers cache';

    /**
     * @var SocialProvidersLoader
     */
    protected $loader;

    /**
     * CacheRefreshCommand constructor.
     *
     * @param  SocialProvidersLoader  $loader
     */
    public function __construct(SocialProvidersLoader $loader)
    {
        parent::__construct();

        $this->loader = $loader;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->loader->forgetSocialProviders();
        $providers = $this->loader->getSocialProviders();

        $this->info(
            'Cache was refreshed. Current available social providers: '.
            $providers->implode('label', ', ')
        );
    }
}
