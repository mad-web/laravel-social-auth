<?php

namespace MadWeb\SocialAuth\Contracts;

use Laravel\Socialite\Contracts\User;

interface SocialAuthenticatable
{
    /**
     * User socials relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function socials();

    /**
     * Check social network is attached to user.
     *
     * @param  string  $slug
     * @return bool
     */
    public function isAttached(string $slug): bool;

    /**
     * Attach social network provider to the user.
     *
     * @param $social
     * @param  string  $socialId
     * @param  string  $token
     * @param  int  $expiresIn
     */
    public function attachSocial($social, string $socialId, string $token, int $expiresIn = null);

    /**
     * Provide ability to modify user data
     * received from social network.
     *
     * @param  User  $socialUser
     * @return array
     */
    public function mapSocialData(User $socialUser);

    /**
     * Get model email field name.
     *
     * @return string
     */
    public function getEmailField(): string;
}
