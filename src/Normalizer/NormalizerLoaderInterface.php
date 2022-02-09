<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Exception\UnexpectedValueException;

/**
 * Interface NormalizerLoaderInterface
 */
interface NormalizerLoaderInterface
{
    /**
     * Get the object normalizer
     *
     * @param class-string<T>|T $className
     *
     * @return NormalizerInterface<T>
     *
     * @throws UnexpectedValueException  if no normalizer has been found
     * @template T as object
     */
    public function getNormalizer($className): NormalizerInterface;

    /**
     * Associate a normalizer to a class name
     *
     * @param class-string $className
     * @param NormalizerInterface $normalizer
     *
     * @return $this
     */
    public function associate($className, NormalizerInterface $normalizer);

    /**
     * Register a normalizer
     *
     * @param NormalizerInterface $normalizer
     *
     * @return $this
     */
    public function addNormalizer(NormalizerInterface $normalizer);
}
