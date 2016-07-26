<?php

namespace Nayjest\Manipulator\DataExtractor;

use Nayjest\Manipulator\DataExtractor\MethodCall\InvalidTransformationsSetupException;
use Nayjest\Manipulator\DataExtractor\MethodCall\MethodNameTransformation;
use Nayjest\StrCaseConverter\Str;

class MethodCall implements DataExtractorInterface
{
    const ORIGINAL_CASE = 1;
    const CAMEL_CASE = 2;
    const SNAKE_CASE = 4;
    /**
     * @var MethodNameTransformation[]
     */
    private $transformations;

    private $cache = [];

    public function __construct(array $transformations)
    {
        $this->transformations = $transformations;
    }

    public function isApplicable($source, $targetName)
    {
        if (!is_object($source)) {
            return false;
        }
        $className = get_class($source);
        if (!array_key_exists($className, $this->cache)) {
            return $this->cache[$className] !== false;
        }
        $nameInCamelCase = null;
        $nameInSnakeCase = null;
        foreach ($this->transformations as $transformation) {
            switch ($transformation->caseConvertingMode) {
                case MethodNameTransformation::ORIGINAL_CASE:
                    $methodName = $transformation->prefix . $targetName . $transformation->suffix;
                    break;
                case self::CAMEL_CASE:
                    if ($nameInCamelCase === null) {
                        $nameInCamelCase = Str::toCamelCase($targetName);
                    }
                    $methodName = $transformation->prefix . $nameInCamelCase . $transformation->suffix;
                    break;
                case self::SNAKE_CASE:
                    if ($nameInSnakeCase === null) {
                        $nameInSnakeCase = Str::toSnakeCase($targetName);
                    }
                    $methodName = $transformation->prefix . $nameInSnakeCase . $transformation->suffix;
                    break;
                default:
                    throw new InvalidTransformationsSetupException;
            }
            if (
                ($transformation->isMagic && method_exists($source, '__call'))
                || method_exists($source, $methodName)
            ) {
                $this->cache[$className] = $methodName;
                return true;
            }
        }
        $this->cache[$className] = false;
        return false;
    }

    public function &extract($source, $targetName, $default)
    {
        return call_user_func([$source, $this->cache[get_class($source)]]);
    }
}
