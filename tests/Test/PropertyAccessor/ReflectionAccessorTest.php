<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_PropertyAccessor
 */
class ReflectionAccessorTest extends TestCase
{
    /**
     * 
     */
    public function test_hydration()
    {
        $object = new BasicClass();

        $hydrator = new ReflectionAccessor(BasicClass::class, 'property');
        $hydrator->write($object, 'value');

        $this->assertEquals('value', $object->property);
    }

    /**
     *
     */
    public function test_extraction()
    {
        $object = new BasicClass('value');

        $hydrator = new ReflectionAccessor(BasicClass::class, 'property');

        $this->assertEquals('value', $hydrator->read($object));
    }

    /**
     *
     */
    public function test_serialization()
    {
        $object = new BasicClass('value');

        $serialized = serialize(new ReflectionAccessor(BasicClass::class, 'property'));

        $this->assertEquals('value', unserialize($serialized)->read($object));
    }
}

class BasicClass
{
    public $property;

    public function __construct($property = null)
    {
        $this->property = $property;
    }
}
