<?php

namespace Nayjest\Manipulator\Test;

use Nayjest\Manipulator\Manipulator;
use Nayjest\Manipulator\Test\Mock\ConstructedWithOneArg;
use Nayjest\Manipulator\Test\Mock\ConstructedWithoutArgs;
use Nayjest\Manipulator\Test\Mock\PropertiesExample;
use Nayjest\Manipulator\Worker;
use PHPUnit_Framework_TestCase;

class WorkerTest extends PHPUnit_Framework_TestCase
{
    /** @var  Worker */
    protected $worker;
    public function setUp()
    {
        $this->worker = Manipulator::getDefaultWorker();
    }

    public function testInstantiateWithoutArguments()
    {
        $instance = $this->worker->instantiate(ConstructedWithoutArgs::class);
        self::assertInstanceOf(ConstructedWithoutArgs::class, $instance);
    }

    public function testInstantiateWithOneArgument()
    {
        $instance = $this->worker->instantiate(ConstructedWithOneArg::class, ['arg1_value']);
        self::assertInstanceOf(ConstructedWithOneArg::class, $instance);
        self::assertEquals('arg1_value', $instance->arg1);
    }

    public function testSetValuesToArray()
    {
        $data = [
            'existing_k' => 'old_v',
        ];
        $result = $this->worker->setMany($data, ['existing_k' => 'replaced_v', 'new_k' => 'new_v']);
        self::assertEquals('replaced_v', $data['existing_k']);
        self::assertEquals('new_v', $data['new_k']);
        self::assertTrue($result === true);
    }

    public function testSetValueToArray()
    {
        $data = [
            'existing_k' => 'old_v',
        ];
        $result1 = $this->worker->set($data, 'existing_k', 'replaced_v');
        $result2 = $this->worker->set($data, 'new_k', 'new_v');
        self::assertEquals('replaced_v', $data['existing_k']);
        self::assertEquals('new_v', $data['new_k']);
        self::assertTrue($result1 === true);
        self::assertTrue($result2 === true);
    }

    public function testGetRecursive()
    {
        self::assertEquals('ok', $this->worker->get(['a.b' => 'ok'], 'a.b'));
        self::assertEquals('ok', $this->worker->get(['a' => ['b'=>'ok']], 'a.b'));
        self::assertEquals('ok', $this->worker->get(['a.b' => ['c'=>'ok']], 'a.b.c'));
        self::assertEquals('ok', $this->worker->get(['a.b.c' => ['d'=>['e.f'=>'ok']]], 'a.b.c.d.e.f'));

        $obj1 = new PropertiesExample();
        $obj2 = new PropertiesExample();
        $obj3 = new PropertiesExample();
        $obj1->setProperty($obj2);
        $obj2->public_property_snake_case = ['a.b' => $obj3];
        $obj3->publicProperty = ['c.d' => 'ok'];
        self::assertEquals('ok', $this->worker->get($obj1, 'property.public_property_snake_case.a.b.publicProperty.c.d'));
    }

    public function testSetValueToObject()
    {
        $obj = new PropertiesExample();
        $this->worker->set($obj, 'property', 1);
        self::assertEquals(1, $obj->getProperty());
        $this->worker->set($obj, 'publicProperty', 2);
        self::assertEquals(2, $obj->publicProperty);
        $this->worker->set($obj, 'public_property_snake_case', 3);
        self::assertEquals(3, $obj->public_property_snake_case);
        $this->worker->set($obj, 'magic', 4);
        self::assertEquals(4, $obj->magic);
        $this->worker->set($obj, 'magic_no_isset', 5);
        self::assertEquals(5, $obj->magic_no_isset);

    }
}