<?php

namespace Bdf\Serializer\PropertyAccessor;

use Bdf\Serializer\PropertyAccessor\Exception\AccessorException;

/**
 * TypedReflectionAccessor
 *
 * Support of php 7.4 typed property: raised a exception when property is undefined.
 * Allows the normalizer to skip those properties and having the same behavior than json_encode.
 */
class TypedPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * The class name
     *
     * @var PropertyAccessorInterface
     */
    private $accessor;

    /**
     * The class name
     *
     * @var string
     */
    private $class;

    /**
     * Property name
     *
     * @var string
     */
    private $property;

    /**
     * Constructor
     *
     * @param PropertyAccessorInterface $accessor
     * @param string $class
     * @param string $property
     */
    public function __construct(PropertyAccessorInterface $accessor, string $class, string $property)
    {
        $this->accessor = $accessor;
        $this->class = $class;
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        try {
            $this->accessor->write($object, $value);
        } catch (\Error $exception) {
            throw new AccessorException('Cannot write value on the property '.$this->class.'::'.$this->property.' on serializer', 0, $exception);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        try {
            return $this->accessor->read($object);
        } catch (\Error $exception) {
            throw new AccessorException('Cannot read value of the property '.$this->class.'::'.$this->property.' on serializer', 0, $exception);
        }
    }
}
