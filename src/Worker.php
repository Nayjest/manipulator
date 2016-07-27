<?php

namespace Nayjest\Manipulator;

use Nayjest\Manipulator\DataExtractor\ArrayDataExtractor;
use Nayjest\Manipulator\DataExtractor\DataExtractorInterface;
use Nayjest\Manipulator\DataExtractor\DirectMethodCall;
use Nayjest\Manipulator\DataExtractor\MethodCall;
use Nayjest\Manipulator\DataExtractor\MethodCall\MethodNameTransformation;
use Nayjest\Manipulator\DataExtractor\ObjectPropertyDataExtractor;
use Nayjest\Manipulator\DataExtractor\RecursiveDataExtractor;
use Nayjest\Manipulator\DataInjector\ArrayDataInjector;
use Nayjest\Manipulator\DataInjector\DataInjectorInterface;
use Nayjest\Manipulator\DataInjector\MagicDataInjector;
use Nayjest\Manipulator\DataInjector\ObjectPropertyDataInjector;
use Nayjest\Manipulator\DataInjector\SetterDataInjector;
use ReflectionClass;

class Worker
{
    /**
     * @var DataExtractorInterface[]
     */
    private $dataExtractors;

    /**
     * @var DataInjectorInterface[]
     */
    private $dataInjectors;

    public function __construct(
        array $dataExtractors = null,
        array $dataInjectors = null
    )
    {
        $this->setDataExtractors(
            $dataExtractors === null ? $this->makeDefaultDataExtractors() : $dataExtractors
        );
        $this->setDataInjectors(
            $dataInjectors === null ? $this->makeDefaultDataInjectors() : $dataInjectors
        );
    }

    function get($source, $name, $default = null)
    {
        if ($source === null) { // optimisation to avoid useless operations
            return $default;
        }
        foreach ($this->dataExtractors as $dataExtractor) {
            if ($dataExtractor->isApplicable($source, $name)) {
                return $dataExtractor->extract($source, $name, $default);
            }
        }
        return $default;
    }

    public function getMany($source, array $names, $default = null)
    {
        $values = [];
        foreach ($names as $name) {
            if (!array_key_exists($name, $values)) {
                $values[$name] = $this->get($source, $name, $default);
            }
        }
        return $values;
    }

    public function set(&$target, $key, $value)
    {
        $result = $this->setMany($target, [$key => $value]);
        return $result === true;
    }

    /**
     * @param $target
     * @param array $values
     * @return string[]|bool returns true or names of not injected fields
     */
    function setMany(&$target, array $values)
    {
        $valuesToInject = $values;
        foreach ($this->dataInjectors as $injector) {
            if ($injector->isApplicable($target, $valuesToInject)) {
                $injected = $injector->inject($target, $valuesToInject);
                if ($injected === true) {
                    return true;
                }
                $valuesToInject = array_diff_key($valuesToInject, array_flip($injected));
            }
        }
        return empty($valuesToInject) ? true : array_keys($valuesToInject);
    }

    public function instantiate($class, array $arguments = [])
    {
        switch (count($arguments)) {
            case 0:
                return new $class();
            case 1:
                return new $class(array_shift($arguments));
            case 2:
                return new $class(
                    array_shift($arguments),
                    array_shift($arguments)
                );
            case 3:
                return new $class(
                    array_shift($arguments),
                    array_shift($arguments),
                    array_shift($arguments)
                );
        }
        $reflection = new ReflectionClass($class);
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @return DataExtractorInterface[]
     */
    public function getDataExtractors()
    {
        return $this->dataExtractors;
    }

    /**
     * @param DataExtractorInterface[] $dataExtractors
     * @return $this
     */
    public function setDataExtractors(array $dataExtractors)
    {
        $this->dataExtractors = $dataExtractors;
        return $this;
    }

    /**
     * @return DataInjectorInterface[]
     */
    public function getDataInjectors()
    {
        return $this->dataInjectors;
    }

    /**
     * @param DataInjectorInterface[] $dataInjectors
     */
    public function setDataInjectors($dataInjectors)
    {
        $this->dataInjectors = $dataInjectors;
    }

    protected function makeDefaultDataExtractors()
    {
        return [
            'array' => new ArrayDataExtractor(),
            'property' => new ObjectPropertyDataExtractor(),
            'recursive' => new RecursiveDataExtractor($this, '.'),
            'method' => new DirectMethodCall(),
            'getter_isser' => new MethodCall([
                new MethodNameTransformation(MethodNameTransformation::CAMEL_CASE, 'get'),
                new MethodNameTransformation(MethodNameTransformation::CAMEL_CASE, 'is'),
            ]),
        ];
    }

    protected function makeDefaultDataInjectors()
    {
        return [
            'array' => new ArrayDataInjector(),
            'property' => new ObjectPropertyDataInjector(false),
            'setter' => new SetterDataInjector(),
            'magic' => new MagicDataInjector(),
        ];
    }
}
