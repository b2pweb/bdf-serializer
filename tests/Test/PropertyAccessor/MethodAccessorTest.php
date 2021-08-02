<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 *
 */
class MethodAccessorTest extends TestCase
{
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

    /**
     *
     */
    public function test_desactivation_write()
    {
        $this->expectException(InvalidArgumentException::class);

        $accessor = new MethodAccessor(TestMethodAccessor::class, 'id');
        $object = new TestMethodAccessor();

        $accessor->write($object, 1);
    }

    /**
     *
     */
    public function test_desactivation_read()
    {
        $this->expectException(InvalidArgumentException::class);

        $accessor = new MethodAccessor(TestMethodAccessor::class, 'id');
        $object = new TestMethodAccessor();

        $accessor->read($object);
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