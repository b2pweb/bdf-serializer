<?php

namespace Bdf\Serializer\PropertyAccessor;

use ReflectionProperty;

/**
 * ReflectionAccessor
 *
 * Use reflection to access object property
 */
class ReflectionAccessor implements PropertyAccessorInterface
{
    /**
     * The class name
     *
     * @var class-string
     */
    private $class;

    /**
     * The property name
     *
     * @var string
     */
    private $property;

    /**
     * The property reflection
     *
     * @var ReflectionProperty
     */
    private $reflection;

    /**
     * Constructor
     *
     * @param class-string $class
     * @param string $property
     */
    public function __construct(string $class, string $property)
    {
        $this->class = $class;
        $this->property = $property;
        $this->reflection = $this->createReflection();
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        $this->reflection->setValue($object, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        return $this->reflection->getValue($object);
    }

    /**
     * Create property accessor.
     *
     * @return ReflectionProperty
     */
    private function createReflection(): ReflectionProperty
    {
        $reflection = new ReflectionProperty($this->class, $this->property);
        PHP_VERSION_ID >= 80100 or $reflection->setAccessible(true);

        return $reflection;
    }

    /**
     * Dont serialize closures
     *
     * @return array
     */
    public function __sleep()
    {
        return ['class', 'property'];
    }

    /**
     * Rebuild reflection.
     */
    public function __wakeup()
    {
        $this->reflection = $this->createReflection();
    }
}
