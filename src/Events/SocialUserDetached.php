<?php

namespace MadWeb\SocialAuth\Events;

use MadWeb\SocialAuth\Contracts\SocialAuthenticatable;

class SocialUserDetached extends SocialEvent
{
    /**
     * @var SocialAuthenticatable
     */
    public $user;

    /**
     * @var object
     */
    public $social;

    /**
     * @var bool
     */
    public $isSuccess;

    /**
     * SocialUserAuthenticated constructor.
     *
     * @param  SocialAuthenticatable  $user
     * @param $social
     * @param  bool  $isSuccess
     */
    public function __construct(SocialAuthenticatable $user, $social, bool $isSuccess)
    {
        $this->user = $user;
        $this->social = $social;
        $this->isSuccess = $isSuccess;
    }
}
