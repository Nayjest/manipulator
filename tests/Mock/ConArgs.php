<?php
namespace Nayjest\Manipulator\Test\Mock;

class ConArgs
{
    public $a;
    public $b;
    public $c;
    protected $d;
    public $e;
    public $f;

    public function __construct($a, $b = null, $e = null, $f = null)
    {
        $this->a = $a;
        $this->b = $b;
        $this->e = $e;
        $this->f = $f;
    }
    public function setD($val)
    {
        $this->d = $val;
    }

    /**
     * @return mixed
     */
    public function getD()
    {
        return $this->d;
    }
}