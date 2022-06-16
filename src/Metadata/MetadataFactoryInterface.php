<?php

namespace Bdf\Serializer\Metadata;

use Bdf\Serializer\Exception\UnexpectedValueException;

/**
 * Interface MetadataFactoryInterface
 */
interface MetadataFactoryInterface
{
    /**
     * Get the related metadata
     *
     * @param class-string<T>|T $className
     *
     * @return ClassMetadata<T>
     * @template T as object
     *
     * @throws UnexpectedValueException  if the class name has no metadata
     */
    public function getMetadata($className): ClassMetadata;
}
