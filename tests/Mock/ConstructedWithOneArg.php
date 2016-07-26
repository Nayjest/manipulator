<?php

namespace Nayjest\Manipulator\Test\Mock;

class ConstructedWithOneArg
{
    public $arg1;

    public function __construct($arg1)
    {
        $this->arg1 = $arg1;
    }
}
