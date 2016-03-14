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
 * Assigns values from array to public properties.
 * By default this function don't creates new properties,
 * but this behavior can be changed if 'true' will be passed to third argument.
 *
 * @param object $instance
 * @param array $fields
 * @param bool $createProperties by default 'false', pass 'true' to create new properties.
 * @return string[] names of successfully assigned properties
 */
function setPublicProperties($instance, array $fields, $createProperties = false)
{
    if ($createProperties) {
        $overwrite = $fields;
    } else {
        $existing = get_object_vars($instance);
        $overwrite = array_intersect_key($fields, $existing);
    }
    foreach ($overwrite as $key => $value) {
        $instance->{$key} = $value;
    }
    return array_keys($overwrite);
}

/**
 * Assigns values from array to corresponding properties using setters.
 *
 * @param object $instance target object
 * @param array $fields
 * @return string[] names of successfully assigned properties
 */
function setValuesUsingSetters($instance, array $fields)
{
    $assignedProperties = [];
    foreach ($fields as $key => $value) {
        $methodName = 'set' . Str::toCamelCase($key);
        if (method_exists($instance, $methodName)) {
            $instance->$methodName($value);
            $assignedProperties[] = $key;
        }
    }
    return $assignedProperties;
}

/**
 * Assigns values from array to object or another array.
 *
 * @param object|array $target
 * @param array $fields
 * @pram int $options supported options: MP_USE_SETTERS, MP_CREATE_PROPERTIES
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
 * Returns names of writable properties.
 *
 * For arrays, keys will be returned.
 *
 * If used with $useSetters option, corresponding property names in snake case will returned.
 *
 * @param object|string|array $src object or class name or array
 * @param bool $useSetters
 * @return array
 */
function getWritable($src, $useSetters = true)
{
    static $writable = [];

    if (is_array($src)) {
        return array_keys($src);
    }
    $class = is_object($src) ? get_class($src) : $src;
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
 * Returns methods with names started by specified keyword
 * and followed by uppercase character.
 *
 * Examples:
 *     - self::getMethodsPrefixedBy('get', $obj)
 *       will return methods that looks like getters.
 *
 * @param string $keyword prefix
 * @param object|string $src object or class name
 * @return array|string[] method names
 */
function getMethodsPrefixedBy($keyword, $src)
{
    $res = [];
    $methods = get_class_methods($src);
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
 * Returns names of setters.
 *
 * @param object|string $src object or class name
 * @return array|string[] method names
 */
function getSetters($src)
{
    return getMethodsPrefixedBy('set', $src);
}

/**
 * Returns names of getters.
 *
 * @param object|string $src object or class name
 * @return array|string[] method names
 */
function getGetters($src)
{
    return getMethodsPrefixedBy('get', $src);
}

/**
 * Returns values of properties specified in $propertyNames argument.
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
 * Extracts value, supports property paths (prop1.prop2.prop3).
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
 * @param mixed $default
 * @param string|null $delimiter
 * @return mixed
 */
function getValue($src, $propertyName, $default = null, $delimiter = '.')
{
    return getValueByRef($src, $propertyName, $default, $delimiter);
}

/**
 * Extracts value by reference, supports property paths (prop1.prop2.prop3).
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
 * @param mixed $default
 * @param string|null $delimiter
 * @return mixed|null
 */
function &getValueByRef(&$src, $propertyName, $default = null, $delimiter = '.')
{
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

    if (is_array($src)) {
        if (array_key_exists($propertyName, $src)) {
            return $src[$propertyName];
        }
    } elseif (is_object($src)) {
        if (isset($src->{$propertyName})) {
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
 * @return bool true if success
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
