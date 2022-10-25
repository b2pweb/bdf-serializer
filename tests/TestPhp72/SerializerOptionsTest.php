<?php

namespace Bdf\Serializer\TestPhp72;

use Bdf\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class SerializerOptionsTest extends TestCase
{
    /**
     *
     */
    public function setUp(): void
    {
        include_once __DIR__.'/Fixtures/entities.php';
    }

    /**
     *
     */
    public function test_serialize_with_null_value()
    {
        $serializer = SerializerBuilder::create()->build();
        $object = new UserNonTyped(1);

        $result = $serializer->toArray($object, ['null' => true]);

        $data = [
            'id' => 1,
            'name' => null,
        ];
        $this->assertEquals($data, $result);

        $result = $serializer->toArray($object);

        $data = ['id' => 1];
        $this->assertEquals($data, $result);
    }
}
