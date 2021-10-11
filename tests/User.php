<?php

namespace MadWeb\SocialAuth\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use MadWeb\SocialAuth\Contracts\SocialAuthenticatable;
use MadWeb\SocialAuth\Traits\UserSocialite;

class User extends Model implements AuthorizableContract, AuthenticatableContract, SocialAuthenticatable
{
    use Authorizable;
    use Authenticatable;
    use UserSocialite;
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'avatar'];
    protected $table = 'users';
}
