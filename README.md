Manipulator
=======

Utilities for performing manipulations with classes and objects.

[![Build Status](https://travis-ci.org/Nayjest/manipulator.svg)](https://travis-ci.org/Nayjest/manipulator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Nayjest/manipulator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Nayjest/manipulator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Nayjest/manipulator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Nayjest/manipulator/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/nayjest/manipulator.svg)](https://packagist.org/packages/nayjest/manipulator)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080/big.png)](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080)

## Installation

The recommended way of installing the component is through [Composer](https://getcomposer.org).

Run following command:

```bash
composer require nayjest/manipulator
```

## Usage

#### `Manipulator::instantiate($class, array $arguments = [])`

Creates class instance using specified constructor arguments.

Method returns instantiated object.

##### Arguments

* string $class &mdash; Target class name
* array $arguments &mdash; Constructor arguments (optional)



#### `Manipulator::assignPublicProperties($instance, array $fields)`

Assigns values from array to existing public properties.

Fields that has no corresponding properties in target object are ignored.

Method returns array containing names of successfully assigned properties.

##### Arguments

* object $instance &mdash; Target object
* array $fields &mdash; Fields to assign. Keys must be same as target object property names.

#### `Manipulator::assignBySetters($instance, array $fields)`

Assigns values from array to corresponding properties using setters.

Fields that has no corresponding properties in target object are ignored.

Method returns names of successfully assigned properties.

##### Arguments

* object $instance &mdash; Target object
* array $fields &mdash; Fields to assign. Keys must be same as target object property names.

##### Example

```php
use Nayjest\Manipulator\Manipulator;

class Target
{
     public function setSomeProperty($value);
}

$target = new Target;

$unassigned = Manipulator::assignBySetters($target, [
    'some_property' => 1,
    'some_other_property' => 2
]);
# Manipulator::assignBySetters() will call $target->setSomeProperty(1).
# Value of 'some_other_property' will be ignored.
# $unassigned will contain array('some_other_property')
```
#### `Manipulator::assign($instance, array $fields)`

Assigns values from array to object. 

This method is just a combination of Manipulator::assignPublicProperties() and Manipulator::assignBySetters().

## Testing

Run following command:

```bash
phpunit
```

## License

© 2014 — 2015 Vitalii Stepanenko

Licensed under the MIT License.

Please see [License File](LICENSE) for more information.
