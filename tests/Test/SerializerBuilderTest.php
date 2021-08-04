<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Normalizer\NormalizerLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_SerializerBuilder
 */
class SerializerBuilderTest extends TestCase
{
    /**
     *
     */
    public function test_metadata_factory_instance()
    {
        $serializer = SerializerBuilder::create()->build();

        $this->assertInstanceOf(Serializer::class, $serializer);
        $this->assertInstanceOf(NormalizerLoader::class, $serializer->getLoader());
    }
}
