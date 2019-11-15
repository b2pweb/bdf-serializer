<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use Bdf\Serializer\Metadata\ClassMetadata;
use ReflectionClass;

/**
 * StaticMethodDriver
 * 
 * call a static method on the reflection class to set the class metadata
 * 
 * @author  Seb
 */
class StaticMethodDriver implements DriverInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * Set the static method name for static method driver
     * 
     * @param string $method
     */
    public function __construct(string $method = 'loadSerializerMetadata')
    {
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        if ($class->isInterface() || !$class->hasMethod($this->method)) {
            return null;
        }
        
        $method = $class->getMethod($this->method);

        if ($method->isAbstract() || !$method->isStatic()) {
            return null;
        }

        $builder = new ClassMetadataBuilder($class);
        $method->invoke(null, $builder);
        
        return $builder->build();
    }
}
