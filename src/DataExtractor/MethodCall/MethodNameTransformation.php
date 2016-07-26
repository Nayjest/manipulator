<?php

namespace Nayjest\Manipulator\DataExtractor\MethodCall;

class MethodNameTransformation
{
    const ORIGINAL_CASE = 1;
    const CAMEL_CASE = 2;
    const SNAKE_CASE = 4;

    /**
     * @var int
     */
    public $caseConvertingMode;
    /**
     * @var string|null
     */
    public $prefix;
    /**
     * @var string|null
     */
    public $suffix;
    /**
     * @var bool
     */
    public $isMagic;

    public function __construct(
        $caseConvertingMode = self::ORIGINAL_CASE,
        $prefix = null,
        $suffix = null,
        $isMagic = false
    )
    {
        $this->caseConvertingMode = $caseConvertingMode;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->isMagic = $isMagic;
    }
}
