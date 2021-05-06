<?php

namespace Bdf\Serializer\PropertyAccessor;

use Bdf\Serializer\Util\AccessorGuesser;
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
     * @var string
     */
    private $getter;

    /**
     * The setter method name
     *
     * @var string
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
     * @param string $entityClass
     * @param string $property
     * @param string $getter
     * @param string $setter
     */
    public function __construct($class, $property, $getter = null, $setter = null)
    {
        $this->property = $property;
        $this->getter = $getter ?: AccessorGuesser::guessGetter($class, $property);
        $this->setter = $setter ?: AccessorGuesser::guessSetter($class, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        if ($this->setter === null) {
            throw new InvalidArgumentException('Could not find setter method for "'.$this->property.'"');
        }

        $object->{$this->setter}($value);
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        if ($this->getter === null) {
            throw new InvalidArgumentException('Could not find getter method for "'.$this->property.'"');
        }

        return $object->{$this->getter}();
    }
}
