<?php

namespace MadWeb\SocialAuth\Test\Utils;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Mockery;

class SocialiteMock
{
    /**
     * @var bool
     */
    protected $request_exception = false;

    /**
     * @var bool
     */
    protected $null_user = false;

    /**
     * @var bool
     */
    protected $with_expires_in = false;

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $email;

    /**
     * SocialiteMock constructor.
     *
     * @param $app
     * @param $email
     */
    public function __construct($app, $email)
    {
        $this->app = $app;
        $this->email = $email;
    }

    /**
     * Mock the Socialite Factory, so we can hijack the OAuth Request.
     *
     * @param  string  $email
     * @param  string  $token
     * @param  string  $id
     * @return Mockery\MockInterface
     */
    public function __invoke($email, $token = 'random-token', $id = 'random-id')
    {
        return $this->create($email, $token, $id);
    }

    /**
     * Mock the Socialite Factory, so we can hijack the OAuth Request.
     *
     * @param  string  $token
     * @param  string  $id
     * @return Mockery\MockInterface
     */
    public function create($token = 'random-token', $id = 'random-id')
    {
        $user = Mockery::mock(\Laravel\Socialite\Two\User::class);

        $user->token = $token;
        if ($this->with_expires_in) {
            $user->expiresIn = 5000;
        }
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getEmail')->andReturn($this->email);
        $user->shouldReceive('getName')->andReturn('John Doe');
        $user->shouldReceive('getNickname')->andReturn('John Doe');
        $user->shouldReceive('getAvatar')->andReturn('http://example.com');
        $user->shouldReceive('getRaw')->andReturn(['verified' => true]);

        $provider = Mockery::mock(\Laravel\Socialite\Two\FacebookProvider::class);

        $expectation = $provider
            ->shouldReceive('user');
        if ($this->request_exception) {
            $expectation
                ->andThrow(\Exception::class);
        } elseif ($this->null_user) {
            $expectation
                ->andReturn(null);
        } else {
            $expectation
                ->andReturn($user);
        }

        $provider
            ->shouldReceive('redirect')
            ->andReturn(new RedirectResponse('https://facebook.com/oauth'));

        $service = Mockery::mock(\Laravel\Socialite\SocialiteManager::class);
        $service
            ->shouldReceive('driver')
            ->andReturn($provider);

        $this->app->instance(Socialite::class, $service);

        return $service;
    }

    /**
     * @return $this
     */
    public function withRequestException()
    {
        $this->request_exception = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withNullUser()
    {
        $this->null_user = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withExpiresIn()
    {
        $this->with_expires_in = true;

        return $this;
    }

    /**
     * @param  string  $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }
}
