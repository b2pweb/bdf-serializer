<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_PropertyAccessor
 */
class PublicAccessorTest extends TestCase
{
    /**
     * 
     */
    public function test_hydration()
    {
        $object = new stdClass();

        $hydrator = new PublicAccessor('', 'property');
        $hydrator->write($object, 'value');

        $this->assertEquals('value', $object->property);
    }

    /**
     *
     */
    public function test_extraction()
    {
        $object = (object)['property' => 'value'];

        $hydrator = new PublicAccessor('', 'property');

        $this->assertEquals('value', $hydrator->read($object));
    }

    /**
     *
     */
    public function test_empty_extraction()
    {
        $object = (object)['property' => null];

        $hydrator = new PublicAccessor('', 'property');

        $this->assertEquals(null, $hydrator->read($object));
    }
}
