<?php

namespace Bdf\Serializer\PropertyAccessor;

/**
 * PublicAccessor
 *
 * Manage public property
 */
class PublicAccessor implements PropertyAccessorInterface
{
    /**
     * Property name
     *
     * @var string
     */
    private $property;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $property
     */
    public function __construct(string $class, string $property)
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        $object->{$this->property} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        return $object->{$this->property};
    }
}
