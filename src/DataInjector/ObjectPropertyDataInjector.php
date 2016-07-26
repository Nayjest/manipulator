<?php

namespace Nayjest\Manipulator\DataInjector;

class ObjectPropertyDataInjector implements DataInjectorInterface
{
    /**
     * @var bool
     */
    private $createsProperties;

    public function __construct($createProperties = false)
    {
        $this->createsProperties = $createProperties;
    }

    public function isApplicable($source, array $values)
    {
        return is_object($source);
    }

    public function inject(&$source, array $values)
    {
        if ($this->createsProperties) {
            $propertiesToInject = $values;
        } else {
            $existing = get_object_vars($source);
            $propertiesToInject = array_intersect_key($values, $existing);
        }
        foreach ($propertiesToInject as $key => $value) {
            $source->{$key} = $value;
        }
        return $this->createsProperties ? true : array_keys($propertiesToInject);
    }

    public function enableCreatingProperties()
    {
        $this->createsProperties = true;
    }

    public function disableCreatingProperties()
    {
        $this->createsProperties = false;
    }

}
