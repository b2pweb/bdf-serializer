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
     * @param string|object $className
     *
     * @return ClassMetadata
     *
     * @throws UnexpectedValueException  if the class name has no metadata
     */
    public function getMetadata($className): ClassMetadata;

}