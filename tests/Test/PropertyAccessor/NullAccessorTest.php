<?php

namespace Bdf\Serializer\PropertyAccessor;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_PropertyAccessor
 */
class NullAccessorTest extends TestCase
{
    /**
     * 
     */
    public function test_hydration()
    {
        $hydrator = new NullAccessor('', 'property');

        $this->assertNull($hydrator->write(null, 'value'));
    }

    /**
     *
     */
    public function test_extraction()
    {
        $hydrator = new NullAccessor('', 'property');

        $this->assertEquals(null, $hydrator->read(null));
    }
}
