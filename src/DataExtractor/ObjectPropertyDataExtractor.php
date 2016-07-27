<?php

namespace Nayjest\Manipulator\DataExtractor;

class ObjectPropertyDataExtractor
{
    public function isApplicable($source, $targetName)
    {
        return isset($source->{$targetName});
    }

    public function extract($source, $targetName, $default)
    {
        if (property_exists($source, $targetName)) {
            return $source->{$targetName};
            // otherwise (it's __get()) calling $src->{$propertyName} will generate PHP notice:
            // indirect modification of overloaded property has no effect.
            // Therefore we return link to temp variable instead of link to variable itself.
        } else {
            $tmp = $source->{$targetName};
            return $tmp;
        }
    }
}
