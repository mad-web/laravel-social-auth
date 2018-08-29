# Social Authentication

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-style]][link-style]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This package give ability to 
 * Sign In 
 * Sign Up
 * Attach/Detach social network provider to the existing account

## Install

_*For Laravel <= 5.4*_ - Via Composer

``` bash
$ composer require mad-web/social-auth:^1.0
```

Via Composer

``` bash
$ composer require mad-web/social-auth
```

_*For Laravel <= 5.4*_ - Now add the service provider in config/app.php file:
```php
'providers' => [
    // ...
    MadWeb\SocialAuth\SocialAuthServiceProvider::class,
];
```

You can publish the migration with:
```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="migrations"
```

The package assumes that your users table name is called "users". If this is not the case you should manually edit the published migration to use your custom table name.

After the migration has been published you can create the social_providers table for storing supported 
providers and user_has_social_provider pivot table by running the migrations:
```bash
$ php artisan migrate
```

You can publish the config-file with:
```bash
$ php artisan vendor:publish --provider="MadWeb\SocialAuth\SocialAuthServiceProvider" --tag="config"
```

This is the contents of the published config/social-auth.php config file:

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

File .env
```ini
FB_ID = <FacebookID>
FB_SECRET = <FacebookSecret>
FB_REDIRECT = <your.domain>/social/facebook/callback

GOOGLE_ID = <GoogleID>
GOOGLE_SECRET = <GoogleSecret>
GOOGLE_REDIRECT = <your.domain>/social/google/callback

GITHUB_ID = <GithubID>
GITHUB_SECRET = <GithubSecret>
GITHUB_REDIRECT = <your.domain>/social/github/callback
```

File config/services.php
```php
    'facebook' => [
        'client_id'     => env('FB_ID'),
        'client_secret' => env('FB_SECRET'),
        'redirect'      => env('FB_REDIRECT')
    ],

    'google' => [
        'client_id'     => env('GOOGLE_ID'),
        'client_secret' => env('GOOGLE_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT')
    ],
    
    'github' => [
        'client_id'     => env('GITHUB_ID'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect'      => env('GITHUB_REDIRECT')
    ]
```

After that, create your social providers in the database

Using console command
```bash
php artisan social-auth:add google --label=Google+
```
Or creating models for example in seeder
```php
SocialProvider::create(['slug' => 'google', 'label' => 'Google+']);
```
Or add rows directly

You can add additional scopes and parameters to the social auth request
```php
SocialProvider::create([
    'label' => 'github',
    'slug' => 'Github',
    'scopes' => ['foo', 'bar'],
    'parameters' => ['foo' => 'bar']
]);
```
To override default scopes
```php
$SocialProvider->setScopes(['foo', 'bar'], true);
```

##### Include social buttons into your templates
```php
 @include('social-auth::attach') // for authenticated user to attach/detach another socials
 @include('social-auth::buttons') // for guests to login via
```

##### Prepare your user model

Implement SocialAuthenticatable interface

Add UserSocialite trait to your User model

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
##### Routes

If you need do some custom with social flow, you should define yourself controllers and 
put your custom url into routes file.

For example 
```php
Route::get('social/{social}', 'Auth\SocialAuthController@getAccount');
Route::get('social/{social}/callback', 'Auth\SocialAuthController@callback');
Route::get('social/{social}/detach', 'Auth\SocialAuthController@detachAccount');
```

In case if you no need any special functionality ypu can use our default controllers

##### Customize for your project

###### Custom User Model
User model we takes from the config('social-auth.models.user');

###### User Fields Mapping
SocialAuthenticatable interface contains method ```mapSocialData``` for mapping social fields for user model
If you need you can redefine this method for your preferences project in your UserModel 

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
[ico-travis]: https://img.shields.io/travis/mad-web/laravel-social-auth/master.svg?style=flat-square
[ico-style]: https://styleci.io/repos/135699653/shield
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mad-web/laravel-social-auth.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mad-web/laravel-social-auth.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mad-web/laravel-social-auth
[link-travis]: https://travis-ci.org/mad-web/laravel-social-auth
[link-style]: https://styleci.io/repos/135699653
[link-code-quality]: https://scrutinizer-ci.com/g/mad-web/laravel-social-auth
[link-downloads]: https://packagist.org/packages/mad-web/laravel-social-auth
[link-author]: https://github.com/mad-web
[link-contributors]: ../../contributors
