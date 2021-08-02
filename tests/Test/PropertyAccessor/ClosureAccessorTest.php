<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_PropertyAccessor
 */
class ClosureAccessorTest extends TestCase
{
    /**
     * 
     */
    public function test_hydration()
    {
        $object = new ClosureClass();

        $hydrator = new ClosureAccessor(ClosureClass::class, 'property');
        $hydrator->write($object, 'value');

        $this->assertEquals('value', $object->property);
    }

    /**
     *
     */
    public function test_extraction()
    {
        $object = new ClosureClass('value');

        $hydrator = new ClosureAccessor(ClosureClass::class, 'property');

        $this->assertEquals('value', $hydrator->read($object));
    }

    /**
     *
     */
    public function test_serialization()
    {
        $object = new ClosureClass('value');

        $serialized = serialize(new ClosureAccessor(ClosureClass::class, 'property'));

        $this->assertEquals('value', unserialize($serialized)->read($object));
    }
}

class ClosureClass
{
    public $property;

    public function __construct($property = null)
    {
        $this->property = $property;
    }
}
