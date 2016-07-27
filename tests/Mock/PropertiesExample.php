<?php

namespace Nayjest\Manipulator\Test\Mock;

class PropertiesExample
{
    protected $property;

    public $publicProperty;

    public $public_property_snake_case;

    protected $initialized_by_method_call;

    protected $magicData = [];

    public function setProperty($val)
    {
        $this->property = $val;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $initialized_by_method_call
     */
    public function initializedByMethodCall($initialized_by_method_call)
    {
        $this->initialized_by_method_call = $initialized_by_method_call;
    }

    public function __set($name, $value)
    {
        if ($name == 'magic' || $name = 'magic_no_isset') {
            $this->magicData[$name] = $value;
        }
    }

    public function __isset($name)
    {
        if ($name === 'magic') {
            return true;
        }
    }

    public function __get($name)
    {
        return isset($this->magicData[$name]) ? $this->magicData[$name] : null;
    }
}