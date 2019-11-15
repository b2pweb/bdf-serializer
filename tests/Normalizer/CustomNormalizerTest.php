<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\SerializerBuilder;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 */
class CustomNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_custom_normalization()
    {
        $serializer = SerializerBuilder::create()->build();
        $serializer->getLoader()->addNormalizer(new MyNormalizer());

        $data = [
            'id'   => 1234,
            'name' => 'Seb',
        ];

        $object = new TestCustomNormalizer();
        $object->id(1234);
        $object->name('Seb');

        $result = $serializer->toArray($object);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_custom_denormalization()
    {
        $serializer = SerializerBuilder::create()->build();
        $serializer->getLoader()->addNormalizer(new MyNormalizer());

        $data = [
            'id'   => 1234,
            'name' => 'Seb',
        ];

        $object = $serializer->fromArray($data, TestCustomNormalizer::class);

        $this->assertEquals($data['id'], $object->id());
        $this->assertEquals($data['name'], $object->name());
    }

}

class TestCustomNormalizer
{
    private $id;
    private $name;

    public function id($id = null)
    {
        if ($id === null) {
            return $this->id;
        }
        $this->id = $id;
    }

    public function name($name = null)
    {
        if ($name === null) {
            return $this->name;
        }
        $this->name = $name;
    }
}

class MyNormalizer implements NormalizerInterface
{

    public function normalize($data, NormalizationContext $context)
    {
        return [
            'id'   => $data->id(),
            'name' => $data->name(),
        ];
    }

    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        if (! ($object = $type->target()) ) {
            $object = new TestCustomNormalizer();
        }

        $object->id($data['id']);
        $object->name($data['name']);

        return $object;
    }

    public function supports(string $className): bool
    {
        return $className === TestCustomNormalizer::class;
    }
}