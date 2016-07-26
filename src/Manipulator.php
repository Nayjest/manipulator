<?php
namespace Nayjest\Manipulator;

/**
 * Manipulator facade.
 */
class Manipulator
{
    protected static $worker;

    private function __construct()
    {
    }

    /**
     * @return Worker
     */
    final public static function getDefaultWorker()
    {
        if (self::$worker === null) {
            self::$worker = new Worker();
        }
        return self::$worker;
    }

    public static function instantiate($class, array $arguments = [])
    {
        return self::getDefaultWorker()->instantiate($class, $arguments);
    }

    public static function set(&$target, $key, $value)
    {
        return self::getDefaultWorker()->set($target, $key, $value);
    }

    function setMany(&$target, array $values)
    {
        return self::getDefaultWorker()->setMany($target, $values);
    }

    function &get(&$source, $name, $default = null)
    {
        return self::getDefaultWorker()->get($source, $name, $default);
    }

    public function getMany($source, array $names)
    {
        return self::getDefaultWorker()->getMany($source, $names);
    }
}