<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;

/**
 * DriverInterface
 */
interface DriverInterface
{
    /**
     * Get the class metadata
     *
     * @param \ReflectionClass $class
     *
     * @return null|ClassMetadata  Returns null if this driver does not support the class
     */
    public function getMetadataForClass(\ReflectionClass $class): ?ClassMetadata;
}