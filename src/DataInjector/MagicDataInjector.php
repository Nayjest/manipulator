<?php

namespace Nayjest\Manipulator\DataInjector;

use Exception;

class MagicDataInjector implements DataInjectorInterface
{
    public function isApplicable($source, array $values)
    {
        return method_exists($source, '__set');
    }

    public function inject(&$source, array $values)
    {
        $injected = [];
        $allInjected = true;
        foreach ($values as $key => $value) {
            try {
                $source->__set($key, $value);
                $injected[] = $key;
            } catch (Exception $e) {
                $allInjected = false;
            }
        }
        return $allInjected ?: $injected;
    }
}
