```
     __             ___       _ _     _
  /\ \ \__ ___   __/ __\_   _(_) | __| | ___ _ __ 
 /  \/ / _` \ \ / /__\// | | | | |/ _` |/ _ \ '__|
/ /\  / (_| |\ V / \/  \ |_| | | | (_| |  __/ |
\_\ \/ \__,_| \_/\_____/\__,_|_|_|\__,_|\___|_|

```

Create and manage your navigation.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-navigation-builder/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-navigation-builder) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-navigation-builder/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-navigation-builder) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-navigation-builder/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-navigation-builder) [![Built for Laravel](https://img.shields.io/badge/Built_for-Laravel-green.svg)](https://laravel.com/) [![License](https://poser.pugx.org/hnhdigital-os/laravel-navigation-builder/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-navigation-builder) [![Donate to this project using Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://patreon.com/RoccoHoward)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-navigation-builder.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-navigation-builder) [![StyleCI](https://styleci.io/repos/72195135/shield?branch=master)](https://styleci.io/repos/72195135) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-navigation-builder)

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Documentation

* [Installation](#install)
* [Configuration](#configuration)
* [Usage](#usage)
* [Contributing](#contributing)
* [Credits](#credits)
* [License](#license)

## Installation

Via composer:

`$ composer require hnhdigital-os/laravel-navigation-builder ~1.0`

## Configuration

Enable the facade by editing config/app.php:

```php
    'aliases' => [
        ...
        'Nav' => HnhDigital\NavigationBuilder\Facade::class,
        ...
    ];
```

The service provider will autoload from Laravel 5.5.

To enable the service provider in versions prior to Laravel 5.4, edit the config/app.php:

Enable the service provider by editing config/app.php:

```php
    'providers' => [
        ...
        HnhDigital\NavigationBuilder\ServiceProvider::class,
        ...
    ];
```

## Usage

(Not available yet!)

See the [wiki](https://github.com/hnhdigital-os/laravel-navigation-builder/wiki) for all usage documentation.

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-navigation-builder/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* Inspired by [Laravel Menu](https://github.com/lavary/laravel-menu)
* [All Contributors](https://github.com/hnhdigital-os/laravel-navigation-builder/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-navigation-builder/blob/master/LICENSE) for more information.
