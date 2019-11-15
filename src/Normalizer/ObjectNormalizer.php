<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\Type\TypeFactory;
use stdClass;

/**
 * ObjectNormalizer
 *
 * @author  Seb
 */
class ObjectNormalizer implements NormalizerInterface, AutoRegisterInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, NormalizationContext $context)
    {
        $hash = $context->assertNoCircularReference($object);

        $normalized = [];

        foreach ((array)$object as $property => $value) {
            $value = $context->root()->normalize($value, $context);

            if ($value === null && !$context->shouldAddNull()) {
                continue;
            }

            $normalized[$property] = $value;
        }

        $context->releaseReference($hash);

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        $object = $this->instantiate($type);

        foreach ((array)$data as $name => $propertyData) {
            $object->$name = $context->root()->denormalize($propertyData, TypeFactory::mixedType(), $context);
        }

        return $object;
    }

    /**
     * Instanciate an object
     *
     * @param Type $type
     *
     * @return stdClass
     */
    private function instantiate($type)
    {
        return $type->target() ?: new stdClass();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return $className === stdClass::class;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTo(NormalizerLoaderInterface $loader): void
    {
        $loader->associate(stdClass::class, $this);
    }
}
