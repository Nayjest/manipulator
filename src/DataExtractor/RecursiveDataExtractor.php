<?php

namespace Nayjest\Manipulator\DataExtractor;

use Exception;
use Nayjest\Manipulator\Worker;

class RecursiveDataExtractor implements DataExtractorInterface
{
    /** @var  Worker */
    private $manipulator;
    private $delimiter;
    private $enabled = true;

    private $position;

    public function __construct(Worker $manipulator, $delimiter = '.')
    {
        $this->manipulator = $manipulator;
        $this->delimiter = $delimiter;
    }

    public function isApplicable($source, $targetName)
    {
        return $this->enabled && $this->position = strpos($targetName, $this->delimiter);
    }

    public function &extract($source, $targetName, $default = null)
    {
        // head(a.b.c) = a
        // tail(a.b.c) = b.c
        $head = substr($targetName, 0, $this->position);
        $tail = substr($targetName, $this->position + 1);
        $this->enabled = false;
        try {
            $nextSource = $this->manipulator->get($source, $head, null);
        } catch (Exception $e) {
            $this->enabled = true;
            throw $e;
        }
        $this->enabled = true;
        if ($nextSource === null) {
            return $default;
        }
        return $this->manipulator->get($nextSource, $tail, $default);
    }
}
