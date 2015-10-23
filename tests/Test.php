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
        $person = Manipulator::instantiate($class);
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
        self::assertEquals(serialize($assigned), serialize(['name', 'age']));
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
        self::assertFalse(property_exists($person, 'nonExistentProp'));
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
        self::assertFalse(property_exists($person, 'someProp'));
        self::assertCount(2, $assigned);
        return $person;
    }

    public function testInstantiateWithArguments()
    {
        $class = 'Nayjest\Manipulator\Test\Mock\ConArgs';
        $inst = Manipulator::instantiate($class, ['a']);
        self::assertEquals('a', $inst->a);
        self::assertEmpty($inst->b);
        self::assertEmpty($inst->e);

        $inst = Manipulator::instantiate($class, ['a', 'b']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEmpty($inst->e);

        $inst = Manipulator::instantiate($class, ['a', 'b', 'e']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEquals('e', $inst->e);
        self::assertEmpty($inst->f);

        $inst = Manipulator::instantiate($class, ['a', 'b', 'e', 'f']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEquals('e', $inst->e);
        self::assertEquals('f', $inst->f);
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

    public function testGetWriteable()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';

        $fields = Manipulator::getWritable($class, false);
        self::assertCount(3, $fields);
        self::assertContains('age', $fields);

        self::assertNotContains('email', $fields);
        $fields = Manipulator::getWritable($class);
        self::assertCount(4, $fields);
        self::assertContains('email', $fields);

        $obj = new PersonStruct();
        $fields = Manipulator::getWritable($obj, false);
        self::assertCount(3, $fields);
        self::assertContains('age', $fields);

        self::assertNotContains('email', $fields);
        $fields = Manipulator::getWritable($obj);
        self::assertCount(4, $fields);
        self::assertContains('email', $fields);

        $fields = Manipulator::getWritable(['a'=>1,'b'=>2]);
        self::assertCount(2, $fields);
        self::assertContains('a', $fields);
        self::assertContains('b', $fields);
    }

    public function testGetValues()
    {
        $src = ['a' => 1,'b' => 2,'c' => 3];
        $res = Manipulator::getValues($src, ['a', 'b', 'c']);
        self::assertEquals($src, $res);

        $res = Manipulator::getValues($src, ['a', 'c']);
        self::assertCount(2, $res);
    }

    public function testGetValue()
    {
        $src = ['a' => 1,'b' => 2,'c' => 3, 'someProp' => 4, 'other_prop' => 5];

        self::assertEquals(1, Manipulator::getValue($src, 'a'));
        self::assertEquals(null, Manipulator::getValue($src, 'd'));
        self::assertEquals('default', Manipulator::getValue($src, 'd', 'default'));

        self::assertEquals(4, Manipulator::getValue($src, 'someProp'));
        self::assertEquals(null, Manipulator::getValue($src, 'some_prop'));

        self::assertEquals(null, Manipulator::getValue($src, 'otherProp'));
        self::assertEquals(5, Manipulator::getValue($src, 'other_prop'));
        $src = (object)$src;
        self::assertEquals(5, Manipulator::getValue($src, 'other_prop'));
        self::assertEquals(null, Manipulator::getValue($src, 'otherProp'));

        $person = new PersonStruct();
        $person->setEmail('text@example.com');
        self::assertEquals(
            'text@example.com',
            Manipulator::getValue($person, 'email')
        );
    }
}
