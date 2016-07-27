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

    public function extract($source, $targetName, $default = null)
    {
//      head(a.b.c) = a
//      tail(a.b.c) = b.c
        $tail = $targetName;
        $head = null;
        $nextSource = null;
        $this->enabled = false;
        $position = $this->position;
        do {
            $pathElement = substr($tail, 0, $position);
            $head = $head ? $head . $this->delimiter . $pathElement : $pathElement;
            $tail = substr($tail, $position + 1);
            try {
                $nextSource = $this->manipulator->get($source, $head, null);
            } catch (Exception $e) {
                $this->enabled = true;
                throw $e;
            }
        } while ($nextSource === null && ($position = strpos($tail, $this->delimiter)));
        $this->enabled = true;
        if ($nextSource === null) {
            return $default;
        }
        $value = $this->manipulator->get($nextSource, $tail, $default);
        return $value;
    }
}
