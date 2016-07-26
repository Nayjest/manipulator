<?php

namespace Nayjest\Manipulator\Test;

use Nayjest\Manipulator\Manipulator;
use Nayjest\Manipulator\Test\Mock\ConstructedWithOneArg;
use Nayjest\Manipulator\Test\Mock\ConstructedWithoutArgs;
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
        $data = [
            'a' => [
                'b' => 'a[b]',//no
                'b.c' => 'a[b.c]',//no
                'b.c2' => 'a[b.c2]'//yes
            ],
            'a.b' => 'a.b',//yes
            'a.b.c' => 'a.b.c'//yes
        ];
        self::assertEquals('a.b', $this->worker->get($data, 'a.b'));
        self::assertEquals('a.b.c', $this->worker->get($data, 'a.b.c'));
        self::assertEquals('a[b.c2]', $this->worker->get($data, 'a.b.c2'));
        self::assertEquals('default', $this->worker->get($data, 'a.b.c.d', 'default'));

        $res = $this->worker->getMany($data, ['a.b', 'a.b.c', 'a.b.c2', 'a.b.c.d'], 'default');
        self::assertTrue($res['a.b'] === 'a.b');
        self::assertTrue($res['a.b.c'] === 'a.b.c');
        self::assertTrue($res['a.b.c2'] === 'a[b.c2]');
        self::assertTrue($res['a.b.c.d'] === 'default');
    }
}