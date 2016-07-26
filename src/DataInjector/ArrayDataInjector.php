<?php

namespace Nayjest\Manipulator\DataInjector;

class ArrayDataInjector implements DataInjectorInterface
{
    public function isApplicable($source, array $values)
    {
        return is_array($source);
    }

    public function inject(&$source, array $values)
    {
        $source = array_merge($source, $values);
        return true;
    }
}
