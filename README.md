Manipulator (mp)
=======

Small library for manipulating PHP objects.


[![Build Status](https://travis-ci.org/Nayjest/Builder.svg?branch=master)](https://travis-ci.org/Nayjest/Builder)
[![Code Coverage](https://scrutinizer-ci.com/g/Nayjest/manipulator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Nayjest/manipulator/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/nayjest/manipulator.svg)](https://packagist.org/packages/nayjest/manipulator)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080/big.png)](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080)


This package provides interface for reading & writing data from various entities like associative arrays, API instances, MVC models, containers.

Manipulator helps when you need to implement common interface for interaction with objects of unknown type.

It's like advanced version of [symfony/property-access](http://symfony.com/doc/current/components/property_access/index.html).

## Requirements

* PHP 5.4+ (hhvm & php7 are supported)

## Installation

The recommended way of installing the component is through [Composer](https://getcomposer.org).

Run following command:

```bash
composer require nayjest/manipulator
```

## Usage

### Basic concepts

##### Workers

Main manipulator functionality can be utilized through [Worker]() class.

You can instantiate your own or use default instance via `Nayjest\Manipulator::getDefaultWorker()`.

*Worker instantiation example:*
```php
use Nayjest\Manipulator\Worker;
// this will create worker with same configuration as default one has.
$worket = new Worker();
```

##### Facade

If default configuration works for you, there is no need of creating worker instances, [Manipulator]() facade has static methods that just calls corresponding methods of default worker.
 
*Facade usage example:*
```php
use Nayjest\Manipulator\Manipulator;

$moderatorOfMainGroup = Manipulator::get($user, 'groups.0.moderator.name');
```

##### Data Accessors & Injectors

@todo

## Testing

This package bundled with PhpUnit tests.

Command for running tests:

```bash
composer test
```

## Contributing

Please see [Contributing Guidelines](contributing.md) and [Code of Conduct](code_of_conduct.md) for details.

## License

© 2014 — 2016 Vitalii Stepanenko

Licensed under the MIT License.

Please see [License File](LICENSE) for more information.
