<?php

namespace Nayjest\Manipulator\Test;

use Nayjest\Manipulator\Manipulator;
use Nayjest\Manipulator\Test\Mock\PersonStruct;
use PHPUnit_Framework_TestCase;

class Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testInstantiateWithoutArguments()
    {
        $class = 'Nayjest\Manipulator\Test\Mock\PersonStruct';
        $class2 = '\Nayjest\Manipulator\Test\Mock\PersonStruct';

        $person = Manipulator::instantiate($class);
        self::assertInstanceOf($class, $person);
        self::assertInstanceOf($class2, $person);


        $person = Manipulator::instantiate($class);
        self::assertInstanceOf($class2, $person);
        self::assertInstanceOf($class, $person);

        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testAssignPublicProperties(PersonStruct $person)
    {
        $assigned = Manipulator::assignPublicProperties($person, [
            'name' => 'John',
            'age' => 27,
        ]);
        self::assertEquals(27, $person->age);
        self::assertEquals('John', $person->name);
        self::assertEquals(serialize($assigned), serialize(['name','age']));
        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testAssignNonExistentProperties(PersonStruct $person)
    {
        $assigned = Manipulator::assignPublicProperties($person, [
            'nonExistentProp' => 'test',
        ]);
        self::assertEmpty($assigned);
        self::assertFalse(property_exists($person,'nonExistentProp'));
        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testAssignUsingSetters(PersonStruct $person)
    {
        $email = 'me@example.com';
        $assigned = Manipulator::assignBySetters(
            $person,
            compact('email')
        );
        self::assertEquals($email, $person->getEmail());
        self::assertArrayHasKey('email', array_flip($assigned));
        self::assertEquals(count($assigned), 1);
        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testAssignMixed(PersonStruct $person)
    {
        $email = 'me2@example.com';
        $someProp = 'test';
        $gender = 'm';

        $assigned = Manipulator::assign(
            $person,
            compact('email', 'someProp', 'gender')
        );
        self::assertEquals($email, $person->getEmail());
        self::assertEquals($gender, $person->gender);
        self::assertFalse(property_exists($person,'someProp'));
        self::assertCount(2, $assigned);
        return $person;
    }

    public function testConArgs()
    {
        $class = 'Nayjest\Manipulator\Test\Mock\ConArgs';
        $inst = Manipulator::instantiate($class, ['a', 'b']);
        self::assertEquals('a!a', $inst->a);
        self::assertEquals('b!b', $inst->b);
    }

    public function testGetSetters()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';
        $setters = Manipulator::getSetters($class);
        self::assertContains('setEmail', $setters);
        self::assertCount(1, $setters);
    }

    public function testGetGetters()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';
        $getters = Manipulator::getGetters($class);
        self::assertContains('getEmail', $getters);
        self::assertCount(1, $getters);
    }
}
