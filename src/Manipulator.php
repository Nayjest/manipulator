<?php
namespace Nayjest\Manipulator;

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
            default:
                $reflection = new ReflectionClass($class);
                return $reflection->newInstanceArgs($arguments);
        }

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
            $methodName = 'set' . self::snakeToCamelCase($key);
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

    protected static function snakeToCamelCase($str)
    {
        return str_replace(
            ' ',
            '',
            ucwords(str_replace(array('-', '_'), ' ', $str))
        );
    }

    protected static function camelToSnakeCase($str)
    {
        $str = lcfirst($str);
        $lowerCase = strtolower($str);
        $result = '';
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $result .= ($str[$i] === $lowerCase[$i] ? '' : '_') . $lowerCase[$i];
        }
        return $result;
    }

    protected static $writable = [];

    /**
     * Returns names of writable properties.
     *
     * @param object|string|array $target
     * @param bool $useSetters
     * @return array
     */
    public static function getWritable($target, $useSetters = true)
    {
        if (is_array($target)) {
            return array_keys($target);
        }
        $class = is_object($target) ? get_class($target) : $target;
        $cacheKey = $class . ($useSetters?'+s':'');
        if (!array_key_exists($cacheKey, self::$writable)) {
            self::$writable[$cacheKey] = array_keys(get_class_vars($class));
            if ($useSetters) {
                $setters = self::getSetters($class);
                foreach ($setters as $setter) {
                    self::$writable[$cacheKey][] = self::camelToSnakeCase(substr($setter, 3));
                }
            }
        }
        return self::$writable[$cacheKey];
    }

    protected static function getXMethods($key, $obj)
    {
        $res = [];
        $methods = get_class_methods($obj);
        foreach ($methods as $method) {
            if (strpos($method, $key) === 0 && strlen($method) > 3 && ctype_upper($method{3})) {
                $res[] = $method;
            }
        }
        return $res;
    }

    public static function getSetters($obj)
    {
        return self::getXMethods('set', $obj);
    }

    public static function getGetters($obj)
    {
        return self::getXMethods('get', $obj);
    }

    /**
     * Returns values of properties specified in $propNames argument.
     *
     * @experimental
     *
     * @param object|array $obj
     * @param string[] $propNames
     * @return array
     */
    public static function getValues($obj, array $propNames)
    {
        $values = [];
        $isArray = is_array($obj);
        foreach ($propNames as $key) {
            if ($isArray) {
                if (array_key_exists($key, $obj)) {
                    $values[$key] = $obj[$key];
                }
            } else {
                if (property_exists($obj, $key)) {
                    $values[$key] = $obj->{$key};
                }
            }
        }
        foreach ($propNames as $key) {
            if (array_key_exists($key, $values)) continue;
            $getter = 'get' . self::snakeToCamelCase($key);
            if (method_exists($obj, $getter)) {
                $values[$key] = $obj->{$getter}();
            }
        }
        return $values;
    }
}
