<?php

namespace Nayjest\Manipulator\DataExtractor;

class ArrayDataExtractor
{
    public function isApplicable($source, $targetName)
    {
        return is_array($source) && array_key_exists($targetName, $source);
    }

    public function extract($source, $targetName, $default)
    {
        return $source[$targetName];
    }
}
