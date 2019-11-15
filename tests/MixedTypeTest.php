<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Mixed
 */
class MixedTypeTest extends TestCase
{
    /**
     *
     */
    public function test_mixed_value_of_an_array()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'name' => 'root',
            'time' => [
                "@type" => "DateTimeImmutable",
                "data" => "2018-03-06T13:45:40+0100",
            ],
            'age' => 12,
        ];

        $expected = [
            'name' => 'root',
            'time' => new \DateTimeImmutable("2018-03-06T13:45:40+0100"),
            'age' => 12,

        ];

        $this->assertEquals($expected, $serializer->fromArray($data, Type::MIXED));
    }
}
