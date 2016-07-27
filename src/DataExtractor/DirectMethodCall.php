<?php

namespace Nayjest\Manipulator\DataExtractor;

/**
 * Data extractor that just calls method of source object that's name is equal to $targetName.
 *
 * DirectMethodCall data extractor never returns default value.
 */
class DirectMethodCall implements DataExtractorInterface
{
    public function isApplicable($source, $targetName)
    {
        // for arrays, scalars and nulls always returns false
        return method_exists($source, $targetName);
    }

    public function extract($source, $targetName, $default)
    {
        return call_user_func([$source, $targetName]);
    }
}
