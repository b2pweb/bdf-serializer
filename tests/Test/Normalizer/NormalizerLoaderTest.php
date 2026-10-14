<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\UnexpectedValueException;
use Bdf\Serializer\Type\Type;
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
     *
     */
    public function test_add_normalizer_prepend()
    {
        $normalizer1 = new class implements NormalizerInterface
        {
            public function normalize($data, NormalizationContext $context) {}

            public function denormalize($data, Type $type, DenormalizationContext $context) {}

            public function supports(string $className): bool
            {
                return true;
            }
        };
        $normalizer2 = new class implements NormalizerInterface
        {
            public function normalize($data, NormalizationContext $context) {}

            public function denormalize($data, Type $type, DenormalizationContext $context) {}

            public function supports(string $className): bool
            {
                return true;
            }
        };

        $loader = $this->getLoader();
        $loader->addNormalizer($normalizer1, true);
        $loader->addNormalizer($normalizer2, true);

        $this->assertEquals($normalizer2, $loader->getNormalizer('unknown'));
    }

    /**
     *
     */
    public function test_add_normalizer_not_prepend()
    {
        $normalizer1 = new class implements NormalizerInterface
        {
            public function normalize($data, NormalizationContext $context) {}

            public function denormalize($data, Type $type, DenormalizationContext $context) {}

            public function supports(string $className): bool
            {
                return true;
            }
        };
        $normalizer2 = new class implements NormalizerInterface
        {
            public function normalize($data, NormalizationContext $context) {}

            public function denormalize($data, Type $type, DenormalizationContext $context) {}

            public function supports(string $className): bool
            {
                return true;
            }
        };

        $loader = $this->getLoader();
        $loader->addNormalizer($normalizer1);
        $loader->addNormalizer($normalizer2);

        $this->assertEquals($normalizer1, $loader->getNormalizer('unknown'));
    }

    /**
     * @return NormalizerLoader
     */
    private function getLoader()
    {
        return new NormalizerLoader([]);
    }
}
