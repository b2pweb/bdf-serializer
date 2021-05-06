<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_PropertyAccessor
 */
class MethodAccessorTest extends TestCase
{
    /**
     *
     */
    public function test_guessing_setter_and_getter()
    {
        $accessor = new MethodAccessor(TestMethodAccessor::class, 'id');

        $object = new TestMethodAccessor();

        $accessor->write($object, 12);
        $this->assertEquals(12, $object->id());

        $accessor->read($object);
        $this->assertEquals(12, $accessor->read($object));
    }

    /**
     *
     */
    public function test_custom_setter_and_getter()
    {
        $accessor = new MethodAccessor(TestCustomMethodAccessor::class, 'id', 'customId', 'setCustomId');

        $object = new TestCustomMethodAccessor();

        $accessor->write($object, 12);
        $this->assertEquals(12, $object->customId());

        $accessor->read($object);
        $this->assertEquals(12, $accessor->read($object));
    }

}


class TestMethodAccessor
{
    private $id;

    public function id()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
class TestCustomMethodAccessor
{
    private $id;

    public function customId()
    {
        return $this->id;
    }

    public function setCustomId($id)
    {
        $this->id = $id;
    }
}