Manipulator (mp)
=======

Small library for manipulating PHP objects.


[![Build Status](https://travis-ci.org/Nayjest/Builder.svg?branch=master)](https://travis-ci.org/Nayjest/Builder)
[![Code Coverage](https://scrutinizer-ci.com/g/Nayjest/manipulator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Nayjest/manipulator/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/nayjest/manipulator.svg)](https://packagist.org/packages/nayjest/manipulator)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080/big.png)](https://insight.sensiolabs.com/projects/4c4b3aa4-e366-456e-8065-67033d2a8080)

It's like
[symfony/property-access](http://symfony.com/doc/current/components/property_access/index.html)
with  more features, faster (no reflection usage) and without over-engineering (~300 lines of code, few functions).

## Requirements

* PHP 5.4+ (hhvm & php7 are supported)

## Installation

The recommended way of installing the component is through [Composer](https://getcomposer.org).

Run following command:

```bash
composer require nayjest/manipulator
```

## Usage

### `Function mp\instantiate`

Creates class instance using specified constructor arguments.

##### Arguments

* string $class &mdash; Target class name
* array $arguments &mdash; Constructor arguments (optional)

##### Returned Value

Function returns instantiated object

##### Example

```php
    $user = \mp\instantiate(MyApp\User::class, [$firstArgument, $secondArgument]);
```

### `Function mp\setPublicProperties`

Assigns values from array to existing public properties of target object.

By default this function ignores fields having no corresponding properties in target object, but this behavior can be changed if TRUE will be passed to third argument.

##### Arguments

* object $targetObject &mdash; target object
* array $fields &mdash; fields to assign, keys must be same as target object property names
* bool $createProperties &mdash;  (optional, default value: false) allows to create new properties in target object if value is TRUE

##### Returned Value

Function returns array containing names of successfully assigned properties.


### `Function mp\setValuesUsingSetters`

Assigns values from array to corresponding properties of target object using setters.

This function works similar to `mp\setPublicProperties()`, but uses setter methods instead of public properties.

Field names may be in snake or camel case, it will be converted to camel case and prefixed by 'set' to check availability of corresponding setter in target object.

Fields having no corresponding setters in target object will be ignored.

This function does not work with magic setters created using __set() php method.

##### Arguments

* object $instance &mdash; target object
* array $fields &mdash; fields to assign, keys are used to check availability of corresponding setters in target object

##### Returned Value

Function returns array containing names of successfully assigned properties.

##### Example

```php
use mp;

class Target
{
     private $somePropery;
     public function setSomeProperty($value)
     {
          $this->someProperty = $value;
     }
     
     public function getSomeProperty()
     {
          return $this->someProperty;
     }
}

$target = new Target;

$result = mp\setValuesUsingSetters($target, [
    'some_property' => 1, // 'someProperty' => 1 will also work
    'some_other_property' => 2
]);
# $target->setSomeProperty(1) will be called.
# Value of 'some_other_property' will be ignored since $target has no 'setSomeOtherProperty' setter.

echo $target->getSomeProperty(); // 1
var_dump($result); // array(0 => 'some_property')
```

### `Function mp\setValues`

Assigns values from $fields array to $target. Target may be object or array.

By default `mp\setValues` ignores fields having no corresponding properties or setters in target object but this behavior can be changed if MP_CREATE_PROPERTIES option is used.

Assigning values using setters can be disabled by removing MP_USE_SETTERS option (it's enabled by default).

When target is an array, `mp\setValues` will call array_merge PHP function.

##### Arguments

* object|array &$target &mdash; target object or array
* array $fields &mdash; fields to assign
* int $options (optional, default value: MP_USE_SETTERS) supported options: MP_USE_SETTERS, MP_CREATE_PROPERTIES

##### Returned Value

Function returns array containing names of successfully assigned properties.

##### Example

```php
use mp;

class Target
{
     private $property1;
     public $property2;
     public function setProperty1($value)
     {
          $this->property1 = $value;
     }
}

$target1 = new Target;
$target2 = new Target;
$target3 = new Target;
$target4 = new Target;

$fieldsToSet = [
    'property1' => 1,
    'property2' => 2,
    'property3' => 3,
];
$result1 = mp\setValues($target1, $fieldsToSet); // MP_USE_SETTERS by default
$result2 = mp\setValues($target1, $fieldsToSet, MP_USE_SETTERS | MP_CREATE_PROPERTIES);
$result3 = mp\setValues($target1, $fieldsToSet, MP_CREATE_PROPERTIES);
$result4 = mp\setValues($target1, $fieldsToSet, 0);
```

Results:

\# | Options | Assigned properties 
--- | --- | ---
1 | not specified (MP_USE_SETTERS by default) | property1, property2
2 | MP_USE_SETTERS \| MP_CREATE_PROPERTIES | property1, property2, property3 (created)
3 | MP_CREATE_PROPERTIES \ |  property2, property3 (created)
4 | 0 |  property2


### `Function mp\getWritable`

Returns names of writable properties for objects and classes or existing keys for arrays.

Only public object properties and properties having setters considered writable.

For setters, this function will return property names based on setter names
(setter names are converted to snake case, 'set' prefixes are removed).

Detecting properties by setters can be disabled by specifying second argument as FALSE.

##### Arguments

* object|string|array $target &mdash; object or class name or array
* bool $useSetters &mdash; (optional, default value: true) if true, properties having setters will be added to results

##### Returned Value

Array containing names of writable properties.

### `Function mp\getMethodsPrefixedBy`

Returns method names from target object/class that starts from specified keyword
and followed by uppercase character.

##### Arguments

* string $keyword &mdash; method name prefix
* object|string $target &mdash; object or class name

##### Returned Value

Array containing method names.

##### Example

```php
class MyClass {
    public function getProperty1(){};
    public function getProperty2(){};
}

$objectMethodNames = \mp\getMethodsPrefixedBy('get', $obj);  // will return methods of $obj that looks like getters
$classMethodNames = \mp\getMethodsPrefixedBy('get', 'MyClass');  // will return methods of 'MyClass' class that looks like getters.
// $classMethodNames will contain ['getProperty1', 'getProperty2']
```

### `Function mp\getSetters`

Returns method names from target object/class that looks like setters.

##### Arguments

* object|string $target &mdash; object or class name

##### Returned Value

Array containing method names.


### `Function mp\getGetters`

Returns method names from target object/class that looks like setters.

##### Arguments

* object|string $target &mdash; object or class name

##### Returned Value

Array containing method names.

### `Function mp\getValues`

Returns values of object properties or array elements specified in $propertyNames argument.

This function supports getters, i. e. value returned by getSomeValue() method of target object can be requested as 'some_value' property.

##### Arguments
 * object|array $src
 * string[] $propertyNames

##### Returned Value

Array containing required values.


### `Function mp\getValue`

Extracts value specified by property / field / method name from object or array.
This function supports property paths (prop1.prop2.prop3) and getters.

 * For $propertyName = 'prop_name', this function will try to extract data in following order from:
 
 - `$src['prop_name']`
 - `$src->prop_name`
 - `$src->getPropName()`
 - `$src->prop_name()`
 - `$src->isPropName()`

##### Arguments

 * array|object $src
 * string $propertyName
 * mixed $default &mdash; (optional, default value: null) default value
 * string|null $delimiter &mdash; (optional, default value: '.') used to specify property paths

### `Function mp\getValueByRef`

Extracts value specified by property / field / method name from object or array by reference if possible.
This function acts like `mp\getValue` with only difference that value will be returned by reference if possible.

### `Function mp\setValue`

Assigns value, supports property paths (prop1.prop2.prop3).

##### Arguments

 * array|object &$target
 * string $propertyName
 * mixed $value
 * string|null $delimiter &mdash; (optional, default value: '.') used to specify property paths
 
##### Returned Value

This function returns TRUE if value was successfully assigned, FALSE otherwise

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
