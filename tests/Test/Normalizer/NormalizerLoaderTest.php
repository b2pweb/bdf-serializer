<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 * @group Bdf_Serializer_Normalizer_Loader
 */
class NormalizerLoaderTest extends TestCase
{
    /**
     *
     */
    public function test_basic_load()
    {
        $normalizer = $this->createMock(PropertyNormalizer::class);
        $normalizer->expects($this->once())->method('supports')->willReturn(true);

        $loader = new NormalizerLoader([$normalizer]);

        $this->assertEquals($normalizer, $loader->getNormalizer('test'));
    }

    /**
     *
     */
    public function test_could_not_find_normalizer()
    {
        $this->expectException(UnexpectedValueException::class);

        $loader = $this->getLoader();
        $loader->getNormalizer('unknown');
    }

    /**
     *
     */
    public function test_associate_normalizer()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);

        $loader = $this->getLoader();
        $loader->associate('unknown', $normalizer);

        $this->assertEquals($normalizer, $loader->getNormalizer('unknown'));
    }

    /**
     *
     */
    public function test_add_normalizer()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->once())->method('supports')->willReturn(true);

        $loader = $this->getLoader();
        $loader->addNormalizer($normalizer);

        $this->assertEquals($normalizer, $loader->getNormalizer('unknown'));
    }

    /**
     * @return NormalizerLoader
     */
    private function getLoader()
    {
        return new NormalizerLoader([]);
    }
}