<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use ReflectionClass;

/**
 * DriverInterface
 */
interface DriverInterface
{
    /**
     * Get the class metadata
     *
     * @param ReflectionClass<T> $class
     *
     * @return null|ClassMetadata<T>  Returns null if this driver does not support the class
     * @template T as object
     */
    public function getMetadataForClass(ReflectionClass $class): ?ClassMetadata;
}
