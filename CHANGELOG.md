# Changelog

All Notable changes to `laravel-social-auth` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 4.0.0 - 2020-12-19

- PHP 8 support
- Dropped older PHP versions support
- Migrated to GitHub Actions

## 3.2.0 - 2020-12-09

Upgraded socialiteproviders/manager to 4.x version

## 3.1.0 - 2020-09-24

Laravel 8 support

## 3.0.0 - 2020-07-17

- Laravel 7 support
- Dropped support for Laravel 5.5 and 5.8
- Minimum required PHP version 7.2.5

## 2.4.0 - 2019-09-07

- Laravel 6.0 support
- Minimum required PHP version upped to 7.2
- Dropped support of unsupported Laravel versions

## 2.3.0 - 2019-03-11

Laravel 5.8 support

## 2.2.0 - 2018-09-23

### Added
- Laravel 5.7 support

### Fixed
- Behavior with empty email from user social provider

## 2.1.0 - 2018-06-01

- Laravel 5.6 support
- CI configs updated

## 2.0.1 - 2018-06-01

- Fix bug with non-existent expiresIn property for `Laravel\Socialite\One\User`

## 2.0.0 - 2017-08-16

### Added
- added support for Laravel 5.5, dropped support for older versions of the framework
- Console command for creating social providers in the database `artisan social-auth:add`
- Added _laravel-_ prefix to the package name

### Changed
- Added _override_scopes_ field to mass assignment
- Moved config and route files from resources folder to root
- Routes now registers by `loadRoutesFrom($dir)` method

## 1.0.1 - 2017-08-02

### Added
- Ability to set additional scopes and parameters
- Ability to user stateless login
- Configuration property to set user model
- Unit tests for events

### Fixed
- Setting table name for SocialProvider model
