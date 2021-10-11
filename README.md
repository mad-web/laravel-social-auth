# Social Authentication

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-actions]][link-acitons]
[![Code Style][ico-style]][link-style]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This package give ability to 
 * Sign In 
 * Sign Up
 * Attach/Detach social network provider to an existing account
 
This package based on [laravel/socialite](https://laravel.com/docs/5.7/socialite) and provide an easy ability
of usage a lot of additional providers from [Socialite Providers](https://socialiteproviders.netlify.com/).

## Install

Via Composer:

``` bash
$ composer require mad-web/laravel-social-auth
```

and add the service provider in config/app.php file:

```php
'providers' => [
    // ...
    MadWeb\SocialAuth\SocialAuthServiceProvider::class,
];
```

Next, publish the migration with:

```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="migrations"
```

The package assumes that your users table name is called "users". If this is not the case you should manually edit the published migration to use your custom table name.

After the migration has been published you can create the `social_providers` table for storing supported 
providers and `user_has_social_provider` pivot table for attaching providers to users by running migrations:

```bash
$ php artisan migrate
```

You can publish the config file with:
```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="config"
```

This is the contents of the published `config/social-auth.php` config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Additional service providers
    |--------------------------------------------------------------------------
    |
    | The social providers listed here will enable support for additional social
    | providers which provided by https://socialiteproviders.github.io/ just
    | add new event listener from the installation guide
    |
    */
    'providers' => [
        //
    ],

    'models' => [
        /*
         * When using the "UserSocialite" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your available social providers. Of course, it
         * is often just the "SocialProvider" model but you may use whatever you like.
         */
        'social' => \MadWeb\SocialAuth\Models\SocialProvider::class,
        
        /*
         * User model which you will use as "SocialAuthenticatable"
         */
        'user' => \App\User::class,
    ],

    'table_names' => [

       /*
       |--------------------------------------------------------------------------
       | Users Table
       |--------------------------------------------------------------------------
       |
       | The table for storing relation between users and social providers. Also there is
       | a place for saving "user social network id", "token", "expiresIn" if it exist
       |
       */
        'user_has_social_provider' => 'user_has_social_provider',

        /*
        |--------------------------------------------------------------------------
        | Social Providers Table
        |--------------------------------------------------------------------------
        |
        | The table that contains all social network providers which your application use.
        |
        */
        'social_providers' => 'social_providers'
    ],

    'foreign_keys' => [

        /*
         * The name of the foreign key to the users table.
         */
        'users' => 'user_id',

        /*
         * The name of the foreign key to the socials table
         */
        'socials' => 'social_id'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication redirection
    |--------------------------------------------------------------------------
    |
    | Redirect path after success/error login via social network
    |
    */
    'redirect' => '/home'
];
```

Or you can publish and modify view templates with:
```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="views"
```

Also you can publish and modify translation file:
```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="lang"
```

##### Add credetials to your project

Add providers to `config/services.php`:
```php
'facebook' => [
    'client_id' => env('FB_ID'),
    'client_secret' => env('FB_SECRET'),
    'redirect' => env('FB_REDIRECT'),
],

'google' => [
    'client_id' => env('GOOGLE_ID'),
    'client_secret' => env('GOOGLE_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT'),
],

'github' => [
    'client_id' => env('GITHUB_ID'),
    'client_secret' => env('GITHUB_SECRET'),
    'redirect' => env('GITHUB_REDIRECT'),
]
```

Add credantials to `.env`:
```ini
FB_ID=
FB_SECRET=
FB_REDIRECT=https://app.domain/social/facebook/callback

GOOGLE_ID=
GOOGLE_SECRET=
GOOGLE_REDIRECT=https://app.domain/social/google/callback

GITHUB_ID=
GITHUB_SECRET=
GITHUB_REDIRECT=https://app.domain/social/github/callback
```

After that, create your social providers in the database.

Using console command:

```bash
php artisan social-auth:add google --label=Google+
```

By model, for example in seeder:

```php
SocialProvider::create(['slug' => 'google', 'label' => 'Google+']);
```
Or add records directly.

You can add additional scopes and parameters to the social auth request:

```php
SocialProvider::create([
    'label' => 'github',
    'slug' => 'Github',
    'scopes' => ['foo', 'bar'],
    'parameters' => ['foo' => 'bar']
]);
```

To override default scopes:

```php
$SocialProvider->setScopes(['foo', 'bar'], true);
```

##### Include social buttons into your templates
```php
 @include('social-auth::attach') // for authenticated user to attach/detach another socials
 @include('social-auth::buttons') // for guests to login via
```

##### Prepare your user model

Implement `SocialAuthenticatable` interface and add `UserSocialite` trait to your User model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MadWeb\SocialAuth\Traits\UserSocialite;
use MadWeb\SocialAuth\Contracts\SocialAuthenticatable;

class User extends Model implements SocialAuthenticatable
{
    use UserSocialite;
   ...
}
```

## Additional Providers

To use any of additional providers from [socialiteproviders.netlify.com](https://socialiteproviders.netlify.com), at first install it:

```bash
composer require socialiteproviders/instagram
```

next, add an event listener from guide to the `social-auth` config file:

```php
/*
|--------------------------------------------------------------------------
| Additional service providers
|--------------------------------------------------------------------------
|
| The social providers listed here will enable support for additional social
| providers which provided by https://socialiteproviders.netlify.com just
| add new event listener from the installation guide
|
*/
'providers' => [
    SocialiteProviders\Instagram\InstagramExtendSocialite::class,
],
...
```

## Customization

### Routes

If you need do some custom with social flow, you should define yourself controllers and 
put your custom url into routes file.

For example:

```php
Route::get('social/{social}', 'Auth\SocialAuthController@getAccount');
Route::get('social/{social}/callback', 'Auth\SocialAuthController@callback');
Route::get('social/{social}/detach', 'Auth\SocialAuthController@detachAccount');
```

In case if you no need any special functionality ypu can use our default controllers.

### Custom User Model

User model we takes from the `social-auth.models.user`.

### User Properties Mapping
`SocialAuthenticatable` interface contains method `mapSocialData` for mapping social fields for user model.
If you need customize a data mapping, you can override this method for your preferences project in the `User` model.

Default mapping method:

```php
public function mapSocialData(User $socialUser)
{
    $raw = $socialUser->getRaw();
    $name = $socialUser->getName() ?? $socialUser->getNickname();
    $name = $name ?? $socialUser->getEmail();

    $result = [
        $this->getEmailField() => $socialUser->getEmail(),
        'name' => $name,
        'verified' => $raw['verified'] ?? true,
        'avatar' => $socialUser->getAvatar(),
    ];

    return $result;
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email madweb.dev@gmail.com instead of using the issue tracker.

## Credits

- [Mad Web](https://github.com/mad-web)
- [All Contributors](link-contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://poser.pugx.org/mad-web/laravel-social-auth/v/stable.svg?format=flat-square
[ico-license]: https://poser.pugx.org/mad-web/laravel-social-auth/license?format=flat-square
[ico-actions]: https://github.com/mad-web/laravel-initializer/workflows/tests/badge.svg
[ico-style]: https://img.shields.io/github/workflow/status/mad-web/laravel-social-auth/Check%20&%20fix%20styling?label=code%20style
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mad-web/laravel-social-auth.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mad-web/laravel-social-auth.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mad-web/laravel-social-auth
[link-actions]: https://github.com/mad-web/laravel-social-auth/actions
[link-style]: https://github.com/mad-web/laravel-social-auth/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain
[link-code-quality]: https://scrutinizer-ci.com/g/mad-web/laravel-social-auth
[link-downloads]: https://packagist.org/packages/mad-web/laravel-social-auth
[link-author]: https://github.com/mad-web
[link-contributors]: ../../contributors
