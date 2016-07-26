<?php

namespace Nayjest\Manipulator;

/**
 * Class Capsule
 * @experimental
 */
class Capsule
{
    private $source;
    /**
     * @var null
     */
    private $manipulatorWorker;

    public function __construct($source, $manipulatorWorker = null)
    {
        $this->source = $source;
        $this->manipulatorWorker = $manipulatorWorker ?: Manipulator::getDefaultWorker();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    public function get($name, $default = null)
    {
        return $this->manipulatorWorker->get($this->source, $name, $default);
    }

    public function set($name, $value = null)
    {
        $values = is_array($name) ? $name : [$name => $value];
        $result = $this->manipulatorWorker->setMany($this->source, $values);
        if ($result !== true) {
            $failedFieldNames = join(',', $result);
            throw new \RuntimeException("Unable to set following properties into Capsule source: [$failedFieldNames]");
        }
        return $this;
    }
}