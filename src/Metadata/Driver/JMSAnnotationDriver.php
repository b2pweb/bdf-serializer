<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use Bdf\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\ClassMetadata as JMSClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata as JMSPropertyMetadata;
use Metadata\ClassMetadata as JMSBaseClassMetadata;
use Metadata\Driver\DriverInterface as JMSDriverInterface;
use ReflectionClass;

/**
 * The annotation driver use JMS annotation
 */
class JMSAnnotationDriver implements DriverInterface
{
    /**
     * @var JMSDriverInterface
     */
    private $driver;

    /**
     * AnnotationDriver constructor.
     *
     * @param JMSDriverInterface $driver
     */
    public function __construct(JMSDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        $builder = new ClassMetadataBuilder($class);
        $jmsMetadata = $this->getJmsMetadata($class);

        if ($this->isJmsMetadataEmpty($jmsMetadata)) {
            return null;
        }

        if ($jmsMetadata instanceof JMSClassMetadata) {
            if (isset($jmsMetadata->postSerializeMethods[0])) {
                $builder->postDenormalization($jmsMetadata->postSerializeMethods[0]->name);
            }
        }

        /** @var JMSPropertyMetadata $metadata */
        foreach ($jmsMetadata->propertyMetadata as $property => $metadata) {
            if ($this->isPropertyStatic($metadata)) {
                continue;
            }

            $propertyBuilder = $builder->add($property);
            $propertyBuilder->alias($metadata->serializedName);
            $propertyBuilder->readOnly($metadata->readOnly);
            $propertyBuilder->inline($metadata->inline);

            if ($metadata->sinceVersion) {
                $propertyBuilder->since($metadata->sinceVersion);
            }
            if ($metadata->untilVersion) {
                $propertyBuilder->until($metadata->untilVersion);
            }
            if ($metadata->getter) {
                $propertyBuilder->readWith($metadata->getter);
            }
            if ($metadata->setter) {
                $propertyBuilder->writeWith($metadata->setter);
            }
            if ($metadata->groups) {
                $propertyBuilder->groups($metadata->groups);
            }
            if ($metadata->type) {
                if (JMSPropertyMetadata::isCollectionList($metadata->type)) {
                    $propertyBuilder->type($metadata->type['params'][0]['name'])->collection();
                } elseif (JMSPropertyMetadata::isCollectionMap($metadata->type)) {
                    $propertyBuilder->type($metadata->type['params'][1]['name'])->collection();
                } else {
                    $propertyBuilder->type($metadata->type['name']);
                }
            }
        }

        return $builder->build();
    }

    /**
     * Gets all the annotations from this class
     *
     * @param ReflectionClass $reflection
     *
     * @return JMSBaseClassMetadata|null
     */
    private function getJmsMetadata(ReflectionClass $reflection): ?JMSBaseClassMetadata
    {
        $metadata = null;

        do {
            $current = $this->driver->loadMetadataForClass($reflection);

            if ($metadata && $current) {
                $current->merge($metadata);
            }

            $metadata = $current;
            $reflection = $reflection->getParentClass();
        } while ($reflection);

        return $metadata;
    }

    /**
     * Checks whether the property of this metadata is static.
     *
     * @param JMSPropertyMetadata $metadata
     *
     * @return bool
     */
    private function isPropertyStatic(JMSPropertyMetadata $metadata): bool
    {
        try {
            $reflection = new \ReflectionProperty($metadata->class, $metadata->name);

            return $reflection->isStatic();
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    /**
     * Check whether the jms metadata has been loaded by default
     *
     * @param JMSBaseClassMetadata|null $jmsMetadata
     *
     * @return bool
     */
    private function isJmsMetadataEmpty(?JMSBaseClassMetadata $jmsMetadata): bool
    {
        if (!$jmsMetadata) {
            return true;
        }

        foreach ($jmsMetadata->propertyMetadata as $metadata) {
            $class = get_class($metadata);
            $default = new $class($metadata->class, $metadata->name);
            $attributes = (array)$metadata;

            unset($attributes['serializedName']);

            foreach ($attributes as $attribute => $value) {
                if ($default->$attribute !== $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
