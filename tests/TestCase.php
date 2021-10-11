<?php

namespace MadWeb\SocialAuth\Test;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use MadWeb\SocialAuth\Test\Utils\SocialiteMock;
use MadWeb\SocialAuth\Test\Utils\TestExceptionHandler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @var string
     */
    protected $userEmail = 'test@user.com';

    /**
     * @var array
     */
    protected $social = ['social' => 'facebook'];

    /**
     * @var SocialiteMock
     */
    protected $socialiteMock;

    public function setUp(): void
    {
        parent::setUp();

        config(['social-auth.models.user' => User::class]);
        $this->setUpDatabase($this->app);

        $this->socialiteMock = new SocialiteMock($this->app, $this->userEmail);
        $this->socialiteMock->create();
    }

    /**
     * @return User
     */
    public function getTestUser(): User
    {
        return User::whereEmail($this->userEmail)->first();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [\MadWeb\SocialAuth\SocialAuthServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('avatar');
        });
        include_once __DIR__.'/../database/migrations/create_social_providers_table.php.stub';

        (new \CreateSocialProvidersTable())->up();

        User::create(['email' => $this->userEmail, 'avatar' => '']);
        $app[config('social-auth.models.social')]->create(['slug' => 'facebook', 'label' => 'Facebook']);
        $app[config('social-auth.models.social')]->create(['slug' => 'google', 'label' => 'Google+']);
    }

    protected function disableExceptionHandling()
    {
        if (! method_exists($this, 'withoutExceptionHandling')) {
            $this->app->instance(ExceptionHandler::class, new TestExceptionHandler());

            return;
        }

        $this->withoutExceptionHandling();
    }
}
