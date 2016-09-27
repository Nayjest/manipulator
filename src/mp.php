<?php

namespace mp;

use Nayjest\StrCaseConverter\Str;
use ReflectionClass;

define('MP_USE_SETTERS', 1);
define('MP_CREATE_PROPERTIES', 2);

/**
 * Creates class instance using specified constructor arguments.
 *
 * @param string $class
 * @param array $arguments
 * @return object
 */
function instantiate($class, array $arguments = [])
{
    switch (count($arguments)) {
        case 0:
            return new $class();
        case 1:
            return new $class(array_shift($arguments));
        case 2:
            return new $class(
                array_shift($arguments),
                array_shift($arguments)
            );
        case 3:
            return new $class(
                array_shift($arguments),
                array_shift($arguments),
                array_shift($arguments)
            );
    }
    $reflection = new ReflectionClass($class);
    return $reflection->newInstanceArgs($arguments);
}

/**
 * Assigns values from array to existing public properties of target object.
 *
 * By default this function ignores fields having no corresponding properties in target object,
 * but this behavior can be changed if TRUE will be passed to third argument.
 *
 * @param object $targetObject target object
 * @param array $fields fields to assign, keys must be same as target object property names
 * @param bool $createProperties (optional, default value: false)
 *                               allows to create new properties in target object if value is true
 * @return string[] names of successfully assigned properties
 */
function setPublicProperties($targetObject, array $fields, $createProperties = false)
{
    if ($createProperties) {
        $overwrite = $fields;
    } else {
        $existing = get_object_vars($targetObject);
        $overwrite = array_intersect_key($fields, $existing);
    }
    foreach ($overwrite as $key => $value) {
        $targetObject->{$key} = $value;
    }
    return array_keys($overwrite);
}

/**
 * Assigns values from array to corresponding properties of target object using setters.
 *
 * This function works similar to mp\setPublicProperties(), but uses setter methods instead of public properties.
 *
 * Field names may be in snake or camel case,
 * it will be converted to camel case and prefixed by 'set'
 * to check availability of corresponding setter in target object.
 *
 * Fields having no corresponding setters in target object will be ignored.
 *
 * This function does not work with magic setters created using __set() php method.
 *
 * @param object $targetObject target object
 * @param array $fields fields to assign, keys are used to check availability of corresponding setters in target object
 * @return string[] names of successfully assigned properties
 */
function setValuesUsingSetters($targetObject, array $fields)
{
    $assignedProperties = [];
    foreach ($fields as $key => $value) {
        $methodName = 'set' . Str::toCamelCase($key);
        if (method_exists($targetObject, $methodName)) {
            $targetObject->$methodName($value);
            $assignedProperties[] = $key;
        }
    }
    return $assignedProperties;
}

/**
 * Assigns values from $fields array to $target. Target may be object or array.
 *
 * By default `mp\setValues` ignores fields having no corresponding properties or setters in target object
 * but this behavior can be changed if MP_CREATE_PROPERTIES option is used.
 *
 * Assigning values using setters can be disabled by removing MP_USE_SETTERS option (it's enabled by default).
 *
 * When target is an array, `mp\setValues` will call array_merge PHP function.
 *
 * @param object|array $target target object or array
 * @param array $fields fields to assign
 * @pram int $options (optional, default value: MP_USE_SETTERS) supported options: MP_USE_SETTERS, MP_CREATE_PROPERTIES
 * @return string[] names of successfully assigned properties
 */
function setValues(&$target, array $fields, $options = MP_USE_SETTERS)
{
    if (is_array($target)) {
        $target = array_merge($target, $fields);
        return array_keys($fields);
    }
    if ($options & MP_USE_SETTERS) {
        $assignedBySetters = setValuesUsingSetters(
            $target,
            $fields
        );
    } else {
        $assignedBySetters = [];
    }
    $assignedProperties = setPublicProperties(
        $target,
        array_diff_key($fields, array_flip($assignedBySetters)),
        $options & MP_CREATE_PROPERTIES
    );

    return array_merge($assignedProperties, $assignedBySetters);
}

/**
 * Returns names of writable properties for objects and classes or existing keys for arrays.
 *
 * Only public object properties and properties having setters considered writable.
 *
 * For setters, this function will return property names based on setter names
 * (setter names are converted to snake case, 'set' prefixes are removed).
 *
 * Detecting properties by setters can be disabled by specifying second argument as FALSE.
 *
 * @param object|string|array $target object or class name or array
 * @param bool $useSetters (optional, default value: true) if true, properties having setters will be added to results
 * @return string[] names of writable properties
 */
function getWritable($target, $useSetters = true)
{
    static $writable = [];

    if (is_array($target)) {
        return array_keys($target);
    }
    $class = is_object($target) ? get_class($target) : $target;
    $cacheKey = $class . ($useSetters ? '+s' : '');
    if (!array_key_exists($cacheKey, $writable)) {
        $writable[$cacheKey] = array_keys(get_class_vars($class));
        if ($useSetters) {
            $setters = getSetters($class);
            foreach ($setters as $setter) {
                $writable[$cacheKey][] = Str::toSnakeCase(substr($setter, 3));
            }
        }
    }
    return $writable[$cacheKey];
}

/**
 * Returns method names from target object/class that starts from specified keyword
 * and followed by uppercase character.
 *
 * Examples:
 *     - mp\getMethodsPrefixedBy('get', $obj)
 *       will return methods that looks like getters.
 *
 * @param string $keyword method name prefix
 * @param object|string $target object or class name
 * @return array|string[] method names
 */
function getMethodsPrefixedBy($keyword, $target)
{
    $res = [];
    $methods = get_class_methods($target);
    $keyLength = strlen($keyword);
    foreach ($methods as $method) {
        if (
            strpos($method, $keyword) === 0
            && strlen($method) > $keyLength
            && ctype_upper($method[$keyLength])
        ) {
            $res[] = $method;
        }
    }
    return $res;
}

/**
 * Returns method names from target object/class that looks like setters.
 *
 * @param object|string $target object or class name
 * @return string[] method names
 */
function getSetters($target)
{
    return getMethodsPrefixedBy('set', $target);
}

/**
 * Returns method names from target object/class that looks like getters.
 *
 * @param object|string $target object or class name
 * @return array|string[] method names
 */
function getGetters($target)
{
    return getMethodsPrefixedBy('get', $target);
}

/**
 * Returns values of properties specified in $propertyNames argument.
 *
 * This function supports getters, i. e.
 * value returned by getSomeValue() method of target object can be requested as 'some_value' property.
 *
 * @experimental
 *
 * @param object|array $src
 * @param string[] $propertyNames
 * @return array
 */
function getValues($src, array $propertyNames)
{

    if (is_array($src)) {
        return array_intersect_key($src, array_flip($propertyNames));
    }
    $values = array_intersect_key(get_object_vars($src), array_flip($propertyNames));
    foreach ($propertyNames as $key) {
        if (array_key_exists($key, $values)) continue;
        $getter = 'get' . Str::toCamelCase($key);
        if (method_exists($src, $getter)) {
            $values[$key] = $src->{$getter}();
        }
    }
    return $values;
}

/**
 * Extracts value specified by property / field name from object or array.
 *
 * This function supports property paths (prop1.prop2.prop3) and getters.
 *
 * If $propertyName = 'prop_name', this method will try to extract data in following order from:
 * - $src['prop_name']
 * - $src->prop_name
 * - $src->getPropName()
 * - $src->prop_name()
 * - $src->isPropName()
 *
 * @experimental
 * @param array|object $src
 * @param string $propertyName
 * @param mixed $default default value
 * @param string|null $delimiter (optional, default value: '.') used to specify property paths
 * @return mixed
 */
function getValue($src, $propertyName, $default = null, $delimiter = '.')
{
    return getValueByRef($src, $propertyName, $default, $delimiter);
}

/**
 * Extracts value specified by property / field / method name from object or array by reference if possible.
 *
 * This function acts like `mp\getValue` with only difference that value will be returned by reference if possible.
 *
 * @experimental
 * @param array|object $src
 * @param string $propertyName
 * @param mixed $default
 * @param string|null $delimiter
 * @return mixed|null
 */
function &getValueByRef(&$src, $propertyName, $default = null, $delimiter = '.')
{
    if (is_array($src) && array_key_exists($propertyName, $src)) {        
        return $src[$propertyName];
    }
    $isObject = is_object($src);
    if ($isObject && isset($src->{$propertyName})) {        
        // if it's not magic method, return reference
        if (property_exists($src, $propertyName)) {
            return $src->{$propertyName};
            // otherwise (it's __get()) calling $src->{$propertyName} will generate PHP notice:
            // indirect modification of overloaded property has no effect.
            // Therefore we return link to temp variable instead of link to variable itself.
        } else {
            $tmp = $src->{$propertyName};
            return $tmp;
        }
    }    
    if ($delimiter && $pos = strpos($propertyName, $delimiter)) {
        // head(a.b.c) = a
        // tail(a.b.c) = b.c
        $head = substr($propertyName, 0, $pos);
        $tail = substr($propertyName, $pos + 1);
        return getValueByRef(
            getValueByRef($src, $head, $default, null),
            $tail,
            $default,
            $delimiter
        );
    }
    if ($isObject) {
        $camelPropName = Str::toCamelCase($propertyName);
        $methods = [
            'get' . $camelPropName,
            $propertyName,
            $camelPropName,
            'is' . $camelPropName
        ];
        foreach ($methods as $method) {
            if (method_exists($src, $method)) {
                $result = call_user_func([$src, $method]);
                return $result;
            }
        }
    }
    return $default;
}

/**
 * Assigns value, supports property paths (prop1.prop2.prop3).
 *
 * @experimental
 * @param array|object $target
 * @param string $propertyName
 * @param mixed $value
 * @param string|null $delimiter
 * @return bool returns TRUE if value was successfully assigned, FALSE otherwise
 */
function setValue(&$target, $propertyName, $value, $delimiter = '.')
{
    if ($delimiter && $pos = strrpos($propertyName, $delimiter)) {
        // head(a.b.c) = a.b
        // tail(a.b.c) = c
        $head = substr($propertyName, 0, $pos);
        $tail = substr($propertyName, $pos + 1);
        $container = &getValueByRef($target, $head, null, $delimiter);
        if (!$container) {
            return false;
        }
        $res = setValues($container, [$tail => $value]);
        return count($res) === 1;
    }
    $res = setValues($target, [$propertyName => $value]);
    return count($res) === 1;
}
