<?php

namespace Bdf\Serializer\PropertyAccessor;

use InvalidArgumentException;

/**
 * MethodAccessor
 *
 * Manage access by a method
 */
class MethodAccessor implements PropertyAccessorInterface
{
    /**
     * The getter method name
     *
     * @var string|null
     */
    private $getter;

    /**
     * The setter method name
     *
     * @var string|null
     */
    private $setter;

    /**
     * The property name
     *
     * @var string
     */
    private $property;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $property
     * @param string|null $getter  Set to false to desactivate
     * @param string|null $setter  Set to false to desactivate
     */
    public function __construct(string $class, string $property, string $getter = null, string $setter = null)
    {
        $this->property = $property;
        $this->getter = $getter;
        $this->setter = $setter;
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        if (!$this->setter) {
            throw new InvalidArgumentException('Could not find setter method for "'.$this->property.'"');
        }

        $object->{$this->setter}($value);
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        if (!$this->getter) {
            throw new InvalidArgumentException('Could not find getter method for "'.$this->property.'"');
        }

        return $object->{$this->getter}();
    }
}
