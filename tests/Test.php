<?php

namespace Nayjest\Manipulator\Test;

use Nayjest\Manipulator\Manipulator;
use Nayjest\Manipulator\Test\Mock\PersonStruct;
use PHPUnit_Framework_TestCase;
use mp;

class Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testInstantiateWithoutArguments()
    {
        $class = 'Nayjest\Manipulator\Test\Mock\PersonStruct';
        $person = mp\instantiate($class);
        self::assertInstanceOf($class, $person);
        return $person;
    }

    public function testSetValuesToArray()
    {
        $data = [
            'a' => 1,
            'b' => 2,
            'c' => 3
        ];
        mp\setValues($data, ['a' => 'a', 'b' => 'b']);
        self::assertEquals('a', $data['a']);
        self::assertEquals('b', $data['b']);
        self::assertEquals(3, $data['c']);
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testSetPublicProperties(PersonStruct $person)
    {
        $assigned = mp\setPublicProperties($person, [
            'name' => 'John',
            'age' => 27,
            'newProperty' => 7,
        ]);
        self::assertEquals(27, $person->age);
        self::assertEquals('John', $person->name);
        self::assertEquals(serialize($assigned), serialize(['name', 'age']));
        self::assertTrue(empty($person->newProperty));
        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testSetNonExistentProperties(PersonStruct $person)
    {
        $assigned = mp\setPublicProperties($person, [
            'nonExistentProp' => 'test',
        ]);
        self::assertEmpty($assigned);
        self::assertFalse(property_exists($person, 'nonExistentProp'));
        return $person;
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     */
    public function testSetNewProperties(PersonStruct $person)
    {
        $assigned = mp\setPublicProperties(
            $person,
            ['newProperty' => 8],
            true
        );
        self::assertEquals(8, $person->newProperty);
        self::assertEquals(serialize($assigned), serialize(['newProperty']));
    }

    /**
     * @depends testInstantiateWithoutArguments
     * @param PersonStruct $person
     * @return PersonStruct
     */
    public function testSetValuesUsingSetters(PersonStruct $person)
    {
        $email = 'me@example.com';
        $assigned = mp\setValuesUsingSetters(
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
    public function testSetValuesMixed(PersonStruct $person)
    {
        $email = 'me2@example.com';
        $someProp = 'test';
        $gender = 'm';

        $assigned = mp\setValues(
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
        $inst = mp\instantiate($class, ['a']);
        self::assertEquals('a', $inst->a);
        self::assertEmpty($inst->b);
        self::assertEmpty($inst->e);

        $inst = mp\instantiate($class, ['a', 'b']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEmpty($inst->e);

        $inst = mp\instantiate($class, ['a', 'b', 'e']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEquals('e', $inst->e);
        self::assertEmpty($inst->f);

        $inst = mp\instantiate($class, ['a', 'b', 'e', 'f']);
        self::assertEquals('a', $inst->a);
        self::assertEquals('b', $inst->b);
        self::assertEquals('e', $inst->e);
        self::assertEquals('f', $inst->f);
    }

    public function testGetSetters()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';
        $setters = mp\getSetters($class);
        self::assertContains('setEmail', $setters);
        self::assertCount(1, $setters);
    }

    public function testGetGetters()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';
        $getters = mp\getGetters($class);
        self::assertContains('getEmail', $getters);
        self::assertCount(1, $getters);
    }

    public function testGetWritable()
    {
        $class = '\Nayjest\Manipulator\Test\Mock\PersonStruct';

        $fields = mp\getWritable($class, false);
        self::assertCount(3, $fields);
        self::assertContains('age', $fields);

        self::assertNotContains('email', $fields);
        $fields = mp\getWritable($class);
        self::assertCount(4, $fields);
        self::assertContains('email', $fields);

        $obj = new PersonStruct();
        $fields = mp\getWritable($obj, false);
        self::assertCount(3, $fields);
        self::assertContains('age', $fields);

        self::assertNotContains('email', $fields);
        $fields = mp\getWritable($obj);
        self::assertCount(4, $fields);
        self::assertContains('email', $fields);

        $fields = mp\getWritable(['a' => 1, 'b' => 2]);
        self::assertCount(2, $fields);
        self::assertContains('a', $fields);
        self::assertContains('b', $fields);
    }

    public function testGetValues()
    {
        $src = ['a' => 1, 'b' => 2, 'c' => 3];
        $res = mp\getValues($src, ['a', 'b', 'c']);
        self::assertEquals($src, $res);

        $res = mp\getValues($src, ['a', 'c']);
        self::assertCount(2, $res);

        $person = new PersonStruct();
        $person->setEmail('text@example.com');
        $person->name = 'John';
        self::assertEquals(
            ['email' => 'text@example.com', 'name' => 'John'],
            mp\getValues($person, ['email', 'name'])
        );
    }

    public function testGetValue()
    {
        $src = ['a' => 1, 'b' => 2, 'c' => 3, 'someProp' => 4, 'other_prop' => 5];

        self::assertEquals(1, mp\getValue($src, 'a'));
        self::assertEquals(null, mp\getValue($src, 'd'));
        self::assertEquals('default', mp\getValue($src, 'd', 'default'));

        self::assertEquals(4, mp\getValue($src, 'someProp'));
        self::assertEquals(null, mp\getValue($src, 'some_prop'));

        self::assertEquals(null, mp\getValue($src, 'otherProp'));
        self::assertEquals(5, mp\getValue($src, 'other_prop'));
        $src = (object)$src;
        self::assertEquals(5, mp\getValue($src, 'other_prop'));
        self::assertEquals(null, mp\getValue($src, 'otherProp'));

        $person = new PersonStruct();
        $person->setEmail('text@example.com');
        self::assertEquals(
            'text@example.com',
            mp\getValue($person, 'email')
        );

        $data = [
            'a' => [
                'b' => [
                    'c' => $person
                ]
            ]
        ];
        self::assertEquals('text@example.com', mp\getValue($data, 'a.b.c.email'));

        mp\getValueByRef($data, 'a.b')['c'] = 'C';
        self::assertEquals('C', mp\getValue($data, 'a.b.c'));
    }

    public function testSetValue()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => []
                ]
            ]
        ];

        $res = mp\setValue($data, 'a.b.c', 7);
        self::assertTrue($res);
        self::assertEquals(7, mp\getValue($data, 'a.b.c'));

        self::assertFalse(mp\setValue($data, 'c.d.b', 8));

        self::assertTrue(mp\setValue($data, 'b', 9));
        self::assertEquals(9, $data['b']);

    }
}
