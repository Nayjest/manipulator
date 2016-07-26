<?php

namespace Nayjest\Manipulator\DataInjector;

use Nayjest\StrCaseConverter\Str;

class SetterDataInjector implements DataInjectorInterface
{
    public function isApplicable($source, array $values)
    {
        return is_object($source);
    }

    public function inject(&$source, array $values)
    {
        $injected = [];
        $allInjected = true;
        foreach ($values as $key => $value) {
            $setter = 'set' . Str::toCamelCase($key);
            if (method_exists($source, $setter)) {
                $source->$setter($value);
                $injected[] = $key;
            } else {
                $allInjected = false;
            }
        }
        return $allInjected ?: $injected;
    }
}
