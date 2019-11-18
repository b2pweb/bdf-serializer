<?php

namespace Bdf\Serializer\Util;

use Bdf\Serializer\PropertyAccessor\ClosureAccessor;
use Bdf\Serializer\PropertyAccessor\PropertyAccessorInterface;
use Bdf\Serializer\PropertyAccessor\DelegateAccessor;
use Bdf\Serializer\PropertyAccessor\PublicAccessor;
use Bdf\Serializer\PropertyAccessor\MethodAccessor;
use Bdf\Serializer\PropertyAccessor\ReflectionAccessor;
use ReflectionClass;

/**
 * AccessorGuesser
 */
class AccessorGuesser
{
    /**
     * Allows closure accessor
     *
     * @var bool
     */
    private static $useClosure = false;

    /**
     * Guess which property accessor suit the property
     *
     * @param ReflectionClass $reflection
     * @param string $property
     * @param array $options
     *
     * @return PropertyAccessorInterface
     */
    public static function guessAccessor(ReflectionClass $reflection, string $property, array $options = null): PropertyAccessorInterface
    {
        $reflection = self::getPropertyOwner($reflection, $property);

        // use reflection accessor if not set. Guess if property is public to use tue public accessor
        if ($options === null) {
            if ($reflection->getProperty($property)->isPublic()) {
                return new PublicAccessor($reflection->name, $property);
            }

            if (self::$useClosure) {
                return new ClosureAccessor($reflection->name, $property);
            }

            return new ReflectionAccessor($reflection->name, $property);
        }

        // If accessor is an array, it should have reader and writer keys.
        // The reader and the writer are methods, the method accessor will be used
        // Otherwise, the delegate accessor will be built with defined accessors.
        if (isset($options['reader']) && is_string($options['reader']) &&
            isset($options['writer']) && is_string($options['writer'])) {
            return new MethodAccessor($reflection->name, $property, $options['reader'], $options['writer']);
        }

        if (isset($options['reader'])) {
            if ($options['reader'] instanceof PropertyAccessorInterface) {
                $reader = $options['reader'];
            } else {
                $reader = new MethodAccessor($reflection->name, $property, $options['reader']);
            }
        } else {
            $reader = static::guessAccessor($reflection, $property);
        }

        if (isset($options['writer'])) {
            if ($options['writer'] instanceof PropertyAccessorInterface) {
                $writter = $options['writer'];
            } else {
                $writter = new MethodAccessor($reflection->name, $property, null, $options['writer']);
            }
        } else {
            $writter = static::guessAccessor($reflection, $property);
        }

        return new DelegateAccessor($reader, $writter);
    }

    /**
     * Try to guess the setter method
     *
     * @param string $class
     * @param string $property
     *
     * @return null|string
     *
     * @todo Manage magic method __set
     */
    public static function guessSetter(string $class, string $property)
    {
        $method = 'set'.ucfirst($property);

        if (method_exists($class, $method)) {
            return $method;
        }
    }

    /**
     * Try to guess the getter method
     *
     * @param string $class
     * @param string $property
     *
     * @return null|string
     *
     * @todo Manage magic method __get
     */
    public static function guessGetter(string $class, string $property)
    {
        if (method_exists($class, $property)) {
            return $property;
        }

        $method = 'get'.ucfirst($property);

        if (method_exists($class, $method)) {
            return $method;
        }
    }

    /**
     * Enable/disable the closure accessor
     *
     * @param bool $flag
     */
    public static function useClosureAccessor($flag): void
    {
        self::$useClosure = (bool)$flag;
    }

    /**
     * Get the reflection that ownes the property
     *
     * @param ReflectionClass $reflection
     * @param string $property
     *
     * @return ReflectionClass
     *
     * @throws \LogicException if property does not belongs to this hierarchy
     */
    private static function getPropertyOwner($reflection, $property): ReflectionClass
    {
        do {
            if ($reflection->hasProperty($property)) {
                return $reflection;
            }
        } while ($reflection = $reflection->getParentClass());

        throw new \LogicException('No reflection found for property "'.$property.'"');
    }
}