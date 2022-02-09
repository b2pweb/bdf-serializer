<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Type\Type;

/**
 * Interface NormalizerInterface
 *
 * @template T
 */
interface NormalizerInterface
{
    /**
     * Normalize data
     *
     * @param T $data
     * @param NormalizationContext $context
     *
     * @return mixed
     */
    public function normalize($data, NormalizationContext $context);

    /**
     * Denormalize data
     *
     * @param mixed $data
     * @param Type<T> $type
     * @param DenormalizationContext $context
     *
     * @return T
     */
    public function denormalize($data, Type $type, DenormalizationContext $context);

    /**
     * Check whether the normalizer support the class name
     *
     * @param class-string $className
     *
     * @return boolean
     * @psalm-assert-if-true T $className
     */
    public function supports(string $className): bool;
}
