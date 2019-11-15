<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\CircularReferenceException;
use Bdf\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 * @group Bdf_Serializer_Normalizer_Object
 */
class ObjectNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_call_object_normalizer()
    {
        $serializer = $this->getSerializer();

        $object = (object) ['id' => 1];

        $this->assertInstanceOf(ObjectNormalizer::class, $serializer->getLoader()->getNormalizer($object));
    }

    /**
     *
     */
    public function test_normalization()
    {
        $serializer = $this->getSerializer();

        $data = [
            'id' => 12,
            'name' => 'foo',
            'roles' => [1, 2]
        ];

        $object = (object) $data;

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_denormalization()
    {
        $serializer = $this->getSerializer();

        $data = [
            'id' => 12,
            'name' => 'foo',
            'roles' => [1, 2]
        ];

        $object = (object) $data;

        $this->assertEquals($object, $serializer->fromArray($data, stdClass::class));
    }

    /**
     *
     */
    public function test_denormalization_in_target()
    {
        $serializer = $this->getSerializer();

        $expected = (object) [
            'id' => 12,
            'name' => 'foo',
            'roles' => [1, 2],
            'other' => 1,
        ];

        $target = (object) [
            'id' => 12,
            'name' => 'test',
            'other' => 1,
        ];

        $data = [
            'name' => 'foo',
            'roles' => [1, 2]
        ];

        $this->assertEquals($expected, $serializer->fromArray($data, $target));
    }

    /**
     *
     */
    public function test_circular_reference()
    {
        $this->expectException(CircularReferenceException::class);

        $serializer = $this->getSerializer();

        $object = (object) [
            'ref' => null,
        ];
        $object->ref = $object;

        $serializer->toArray($object, [NormalizationContext::CIRCULAR_REFERENCE_LIMIT => 2]);
    }

    /**
     *
     */
    public function test_circular_reference2()
    {
        $serializer = $this->getSerializer();

        $collection = (object) ['refs' => []];
        $item = (object)['name' => 'foo'];
        $collection->refs[] = $item;
        $collection->refs[] = $item;

        $data = $serializer->toArray($collection);

        $this->assertSame(['refs' => [['name' => 'foo'], ['name' => 'foo']]], $data);
    }

    /**
     *
     */
    public function test_supports()
    {
        $normalizer = new ObjectNormalizer();

        $this->assertTrue($normalizer->supports(stdClass::class));
        $this->assertFalse($normalizer->supports('unknown'));
    }

    /**
     * @return \Bdf\Serializer\Serializer
     */
    private function getSerializer()
    {
        return SerializerBuilder::create()
            ->setNormalizers([new ObjectNormalizer()])
            ->build();
    }

}