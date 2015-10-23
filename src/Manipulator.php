<?php
namespace Nayjest\Manipulator;

use Nayjest\StrCaseConverter\Str;
use ReflectionClass;

/**
 * Class Manipulator
 *
 * Utilities for performing manipulations with classes and objects.
 *
 */
class Manipulator
{
    /**
     * Creates class instance using specified constructor arguments.
     *
     * @param string $class
     * @param array $arguments
     * @return object
     */
    public static function instantiate($class, array $arguments = [])
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
     * Assigns values from array to existing public properties.
     *
     * @param object $instance
     * @param array $fields
     * @return string[] names of successfully assigned properties
     */
    public static function assignPublicProperties($instance, array $fields)
    {
        $existing = get_object_vars($instance);
        $overwrite = array_intersect_key($fields, $existing);
        foreach ($overwrite as $key => $value) {
            $instance->{$key} = $value;
        }
        return array_keys($overwrite);
    }

    /**
     * Assigns values from array to corresponding properties using setters.
     *
     * @param object $instance
     * @param array $fields
     * @return string[] names of successfully assigned properties
     */
    public static function assignBySetters($instance, array $fields)
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
     * Assigns values from array to object.
     *
     * @param object $instance
     * @param array $fields
     * @return string[] names of successfully assigned properties
     */
    public static function assign($instance, array $fields)
    {
        $assigned_fields = self::assignPublicProperties($instance, $fields);
        $fields = array_diff_key($fields, array_flip($assigned_fields));
        return array_merge(
            $assigned_fields,
            self::assignBySetters($instance, $fields)
        );
    }

    protected static $writable = [];

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
    public static function getWritable($src, $useSetters = true)
    {
        if (is_array($src)) {
            return array_keys($src);
        }
        $class = is_object($src) ? get_class($src) : $src;
        $cacheKey = $class . ($useSetters?'+s':'');
        if (!array_key_exists($cacheKey, self::$writable)) {
            self::$writable[$cacheKey] = array_keys(get_class_vars($class));
            if ($useSetters) {
                $setters = self::getSetters($class);
                foreach ($setters as $setter) {
                    self::$writable[$cacheKey][] = Str::toSnakeCase(substr($setter, 3));
                }
            }
        }
        return self::$writable[$cacheKey];
    }

    /**
     * Returns methods with names started by specified keyword
     * and followed by uppercase character.
     *
     * Examples:
     *     - self::getMethodsByPrefix('get', $obj)
     *       will return methods that looks like getters.
     *
     * @param string $keyword prefix
     * @param object|string $src object or class name
     * @return array|string[] method names
     */
    protected static function getMethodsByPrefix($keyword, $src)
    {
        $res = [];
        $methods = get_class_methods($src);
        $keyLength = strlen($keyword);
        foreach ($methods as $method) {
            if (
                strpos($method, $keyword) === 0
                && strlen($method) > $keyLength
                && ctype_upper($method[$keyLength])) {
                $res[] = $method;
            }
        }
        return $res;
    }

    /**
     * Returns names of setters.
     *
     * @param object|string $src object or class name
     * @return array|\string[] method names
     */
    public static function getSetters($src)
    {
        return self::getMethodsByPrefix('set', $src);
    }

    /**
     * Returns names of getters.
     *
     * @param object|string $src object or class name
     * @return array|\string[] method names
     */
    public static function getGetters($src)
    {
        return self::getMethodsByPrefix('get', $src);
    }

    /**
     * Returns values of properties specified in $propNames argument.
     *
     * @experimental
     *
     * @param object|array $src
     * @param string[] $propNames
     * @return array
     */
    public static function getValues($src, array $propNames)
    {
        $values = [];
        $isArray = is_array($src);
        foreach ($propNames as $key) {
            if ($isArray) {
                if (array_key_exists($key, $src)) {
                    $values[$key] = $src[$key];
                }
            } else {
                // @todo: possible bug
                // property_exists may return private/protected properties
                if (property_exists($src, $key)) {
                    $values[$key] = $src->{$key};
                }
            }
        }
        foreach ($propNames as $key) {
            if (array_key_exists($key, $values)) continue;
            $getter = 'get' . Str::toCamelCase($key);
            if (method_exists($src, $getter)) {
                $values[$key] = $src->{$getter}();
            }
        }
        return $values;
    }

    /**
     * Extracts value.
     *
     * If $propName = 'prop_name', this method will try to extract data in following order from:
     * - $src['prop_name']
     * - $src->prop_name
     * - $src->getPropName()
     * - $src->prop_name()
     * - $src->isPropName()
     *
     * @experimental
     * @param $src
     * @param string $propName
     * @param $default
     * @return mixed
     */
    public static function getValue($src, $propName, $default = null)
    {
        if (is_array($src)) {
            if (array_key_exists($propName, $src)) {
                return $src[$propName];
            }
        } elseif (is_object($src)) {
            if (isset($src->{$propName})) {
                return $src->{$propName};
            }

            $camelPropName = Str::toCamelCase($propName);
            $methods = [
                'get' . $camelPropName,
                $propName,
                $camelPropName,
                'is' . $camelPropName
            ];
            foreach ($methods as $method) {
                if (method_exists($src, $method)) {
                    return call_user_func([$src, $method]);
                }
            }
        }
        return $default;
    }
}
