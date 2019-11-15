<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\Type\TypeFactory;
use Traversable;

/**
 * TraversableNormalizer
 */
class TraversableNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($data, NormalizationContext $context)
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$key] = $context->root()->normalize($value, $context);
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        $className = $type->name();
        $denormalized = new $className;

        foreach ((array)$data as $key => $value) {
            $denormalized[$key] = $context->root()->denormalize(
                $value,
                $type->isParametrized() ? $type->subType() : TypeFactory::mixedType(),
                $context
            );
        }

        return $denormalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return is_subclass_of($className, Traversable::class);
    }
}
