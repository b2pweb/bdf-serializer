<?php

namespace Bdf\Serializer\Normalizer;

use ArrayObject;
use Bdf\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 * @group Bdf_Serializer_Normalizer_Traversable
 */
class TraversableNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_call_closure_normalizer()
    {
        $serializer = $this->getSerializer();

        $object = new ArrayObject();

        $this->assertInstanceOf(TraversableNormalizer::class, $serializer->getLoader()->getNormalizer($object));
    }

    /**
     *
     */
    public function test_normalization()
    {
        $serializer = $this->getSerializer();

        $data = ['id' => 1];
        $object = new ArrayObject([$data]);

        $this->assertEquals([['id' => 1]], $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_denormalization()
    {
        $serializer = $this->getSerializer();

        $data = ['id' => 1];

        /** @var ArrayObject $collection */
        $collection = $serializer->fromArray($data, ArrayObject::class);

        $this->assertEquals($data, $collection->getArrayCopy());
    }

    /**
     *
     */
    public function test_denormalization_with_parameter_type()
    {
        $serializer = $this->getSerializer();
        $serializer->getLoader()->addNormalizer(new DateTimeNormalizer());

        $data = [
            '2018-03-25 15:23:06',
            '2018-04-12 12:45:32',
            '2018-04-18 23:41:02',
        ];

        /** @var ArrayObject $collection */
        $collection = $serializer->fromArray($data, 'ArrayObject<DateTime>');

        $this->assertEquals(new ArrayObject([
            new \DateTime('2018-03-25 15:23:06'),
            new \DateTime('2018-04-12 12:45:32'),
            new \DateTime('2018-04-18 23:41:02'),
        ]), $collection);
    }

    /**
     *
     */
    public function test_supports()
    {
        $normalizer = new TraversableNormalizer();

        $this->assertTrue($normalizer->supports(ArrayObject::class));
        $this->assertFalse($normalizer->supports('unknown'));
    }

    /**
     * @return \Bdf\Serializer\Serializer
     */
    private function getSerializer()
    {
        return SerializerBuilder::create()
            ->setNormalizers([new TraversableNormalizer()])
            ->build();
    }

}