Manipulator (mp)
=======

Small PHP library for manipulating data structures (objects, multidimensional arrays, etc).


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

##### `mp\instantiate(string $class, array $arguments = [])`

Creates class instance using specified constructor arguments.

Method returns instantiated object.

###### Arguments

* string $class &mdash; Target class name
* array $arguments &mdash; Constructor arguments (optional)



##### `mp\assignPublicProperties(object $instance, array $fields)`

Assigns values from array to existing public properties.

Fields that has no corresponding properties in target object are ignored.

Method returns array containing names of successfully assigned properties.

###### Arguments

* object $instance &mdash; Target object
* array $fields &mdash; Fields to assign. Keys must be same as target object property names.



##### `mp\assignValuesBySetters(object $instance, array $fields)`

Assigns values from array to corresponding properties using setters.

Fields that has no corresponding properties in target object are ignored.

Method returns names of successfully assigned properties.

###### Arguments

* object $instance &mdash; Target object
* array $fields &mdash; Fields to assign. Keys must be same as target object property names.

###### Example

```php
use mp;

class Target
{
     public function setSomeProperty($value);
}

$target = new Target;

$unassigned = mp\assignValuesBySetters($target, [
    'some_property' => 1,
    'some_other_property' => 2
]);
# mp\assignValuesBySetters() will call $target->setSomeProperty(1).
# Value of 'some_other_property' will be ignored.
# $unassigned will contain array('some_other_property')
```

##### `mp\assignValues(&$target, array $fields)`

Assigns values from array to object. 

This method is just a combination of mp\assignPublicProperties() and mp\assignValuesBySetters().



##### `mp\getWritable($src, $useSetters = true)`

Returns names of writable properties.

###### Arguments

* object|string|array $src &mdash; object or class name or array
* bool $useSetters &mdash; if true, protected/private properties with corresponding setters will be added to result

##### `mp\getMethodsPrefixedBy(string $keyword, object|string $src)`


##### `mp\getSetters(object|string $src)`


##### `mp\getGetters(object|string $src)`


##### `mp\getValues($src, array $propertyNames)`


##### `mp\getValue($src, $propertyName, $default = null, $delimiter = '.')`

Extracts value, supports property paths (prop1.prop2.prop3).


##### `mp\&getValueByRef(&$src, $propertyName, $default = null, $delimiter = '.')`

Extracts value by reference, supports property paths (prop1.prop2.prop3).


##### `mp\assignValue(&$target, $propertyName, $value, $delimiter = '.')`

Assigns value, supports property paths (prop1.prop2.prop3).



## Testing

#### Overview

The package bundled with phpunit tests.

#### Running Unit Tests

Just execute phpunit from package folder.

```bash
phpunit
```
Package dependencies must be installed via composer (just run `composer install`).

## License

© 2014 — 2015 Vitalii Stepanenko

Licensed under the MIT License.

Please see [License File](LICENSE) for more information.
