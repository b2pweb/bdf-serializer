<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Type\Type;

/**
 * Interface NormalizerInterface
 */
interface NormalizerInterface
{
    /**
     * Normalize data
     *
     * @param mixed  $data
     * @param NormalizationContext $context
     *
     * @return mixed
     */
    public function normalize($data, NormalizationContext $context);

    /**
     * Denormalize data
     *
     * @param mixed   $data
     * @param Type    $type
     * @param DenormalizationContext $context
     *
     * @return mixed
     */
    public function denormalize($data, Type $type, DenormalizationContext $context);

    /**
     * Check whether the normalizer support the class name
     *
     * @param string $className
     *
     * @return boolean
     */
    public function supports(string $className): bool;
}