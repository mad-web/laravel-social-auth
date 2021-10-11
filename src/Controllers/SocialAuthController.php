<?php

namespace MadWeb\SocialAuth\Controllers;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Contracts\User as SocialUser;
use MadWeb\SocialAuth\Events\SocialUserAuthenticated;
use MadWeb\SocialAuth\Events\SocialUserDetached;
use MadWeb\SocialAuth\Exceptions\SocialGetUserInfoException;
use MadWeb\SocialAuth\Exceptions\SocialUserAttachException;
use MadWeb\SocialAuth\Models\SocialProvider;
use MadWeb\SocialAuth\SocialProviderManager;

/**
 * Class SocialAuthController.
 */
class SocialAuthController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use RedirectsUsers;

    /**
     * Redirect path.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * @var Guard auth provider instance
     */
    protected $auth;

    /**
     * @var Socialite
     */
    protected $socialite;

    /**
     * @var \MadWeb\SocialAuth\Contracts\SocialAuthenticatable|\Illuminate\Contracts\Auth\Authenticatable
     */
    protected $userModel;

    /**
     * @var SocialProviderManager
     */
    protected $manager;

    /**
     * SocialAuthController constructor. Register Guard contract dependency.
     *
     * @param  Guard  $auth
     * @param  Socialite  $socialite
     */
    public function __construct(Guard $auth, Socialite $socialite)
    {
        $this->auth = $auth;
        $this->socialite = $socialite;
        $this->redirectTo = config('social-auth.redirect');

        $className = config('social-auth.models.user');
        $this->userModel = new $className();

        $this->middleware(function ($request, $next) {
            $this->manager = new SocialProviderManager($request->route('social'));

            return $next($request);
        });
    }

    /**
     * If there is no response from the social network, redirect the user to the social auth page
     * else make create with information from social network.
     *
     * @param  SocialProvider  $social  bound by "Route model binding" feature
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getAccount(SocialProvider $social)
    {
        $provider = $this->socialite->driver($social->slug);

        if (! empty($social->scopes)) {
            $social->override_scopes ? $provider->setScopes($social->scopes) : $provider->scopes($social->scopes);
        }

        return empty($social->parameters) ? $provider->redirect() : $provider->with($social->parameters)->redirect();
    }

    /**
     * Redirect callback for social network.
     *
     * @param  Request  $request
     * @param  SocialProvider  $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws SocialGetUserInfoException
     * @throws SocialUserAttachException
     */
    public function callback(Request $request, SocialProvider $social)
    {
        $provider = $this->socialite->driver($social->slug);

        $SocialUser = null;

        // try to get user info from social network
        try {
            $SocialUser = $social->stateless ? $provider->stateless()->user() : $provider->user();
        } catch (Exception $e) {
            throw new SocialGetUserInfoException($social, $e->getMessage());
        }

        // if we have no social info for some reason
        if (! $SocialUser) {
            throw new SocialGetUserInfoException(
                $social,
                trans('social-auth::messages.no_user_data', ['social' => $social->label])
            );
        }

        // if user is guest
        if (! $this->auth->check()) {
            return $this->processData($request, $social, $SocialUser);
        }

        $redirect_path = $this->redirectPath();
        $User = $request->user();

        // if user already attached
        if ($User->isAttached($social->slug)) {
            throw new SocialUserAttachException(
                redirect($redirect_path)
                    ->withErrors(trans('social-auth::messages.user_already_attach', ['social' => $social->label])),
                $social
            );
        }

        //If someone already attached current socialProvider account
        if ($this->manager->socialUserQuery($SocialUser->getId())->exists()) {
            throw new SocialUserAttachException(
                redirect($redirect_path)
                    ->withErrors(trans('social-auth::messages.someone_already_attach')),
                $social
            );
        }

        $this->manager->attach($User, $SocialUser);

        return redirect($redirect_path);
    }

    /**
     * Detaches social account for user.
     *
     * @param  Request  $request
     * @param  SocialProvider  $social
     * @return array
     *
     * @throws SocialUserAttachException
     */
    public function detachAccount(Request $request, SocialProvider $social)
    {
        /** @var \MadWeb\SocialAuth\Contracts\SocialAuthenticatable $User */
        $User = $request->user();
        $UserSocials = $User->socials();

        if ($UserSocials->count() === 1 and empty($User->{$User->getEmailField()})) {
            throw new SocialUserAttachException(
                back()->withErrors(trans('social-auth::messages.detach_error_last')),
                $social
            );
        }

        $result = $UserSocials->detach($social->id);

        if (! $result) {
            throw new SocialUserAttachException(
                back()->withErrors(trans('social-auth::messages.detach_error', ['social' => $social->label])),
                $social
            );
        }

        event(new SocialUserDetached($User, $social, $result));

        return redirect($this->redirectPath());
    }

    /**
     * Process user using data from social network.
     *
     * @param  Request  $request
     * @param  SocialProvider  $social
     * @param  SocialUser  $socialUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function processData(Request $request, SocialProvider $social, SocialUser $socialUser)
    {
        //Checks by socialProvider identifier if user exists
        $ExistUser = $this->manager->getUserByKey($socialUser->getId());
        $redirect_path = $this->redirectPath();

        //Checks if user exists with current socialProvider identifier, auth if does
        if ($ExistUser) {
            $this->login($ExistUser);

            return redirect($redirect_path);
        }

        //Checks if socialProvider email exists
        if ($social_user_email = $socialUser->getEmail()) {
            //Checks if account exists with socialProvider email, auth and attach current socialProvider if does
            $ExistUser = $this->userModel->where($this->userModel->getEmailField(), $social_user_email)->first();
            if ($ExistUser) {
                $this->login($ExistUser);

                $this->manager->attach($request->user(), $socialUser);

                return redirect($redirect_path);
            }
        }

        //If account for current socialProvider data doesn't exist - create new one
        $NewUser = $this->manager->createNewUser($this->userModel, $social, $socialUser);
        $this->login($NewUser);

        return redirect($redirect_path);
    }

    /**
     * Login user.
     *
     * @param  Authenticatable  $user
     */
    protected function login(Authenticatable $user)
    {
        $this->auth->login($user);
        event(new SocialUserAuthenticated($user));
    }
}
