<?php

namespace Bdf\Serializer\Util;

use Bdf\Serializer\PropertyAccessor\ClosureAccessor;
use Bdf\Serializer\PropertyAccessor\PropertyAccessorInterface;
use Bdf\Serializer\PropertyAccessor\DelegateAccessor;
use Bdf\Serializer\PropertyAccessor\PublicAccessor;
use Bdf\Serializer\PropertyAccessor\MethodAccessor;
use Bdf\Serializer\PropertyAccessor\ReflectionAccessor;
use Bdf\Serializer\PropertyAccessor\TypedPropertyAccessor;
use ReflectionClass;

use function method_exists;

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
     *
     * @deprecated Use getMethodAccessor or getPropertyAccessor instead of
     */
    public static function guessAccessor(ReflectionClass $reflection, string $property, array $options = null): PropertyAccessorInterface
    {
        // use reflection accessor if not set. Guess if property is public to use tue public accessor
        if ($options === null) {
            return self::getPropertyAccessor($reflection, $property);
        }

        return self::getMethodAccessor($reflection, $property, $options['reader'] ?? null, $options['writer'] ?? null, $options['readOnly'] ?? false);
    }

    /**
     * Guess which method accessor suit the property
     *
     * @param ReflectionClass $reflection
     * @param string $property
     * @param PropertyAccessorInterface|string|null $getter
     * @param PropertyAccessorInterface|string|null $setter
     *
     * @return PropertyAccessorInterface
     */
    public static function getMethodAccessor(ReflectionClass $reflection, $property, $getter, $setter, bool $readOnly = false): PropertyAccessorInterface
    {
        // If accessor is an array, it should have reader and writer keys.
        // The reader and the writer are methods, the method accessor will be used
        // Otherwise, the delegate accessor will be built with defined accessors.
        if (is_string($getter) && is_string($setter)) {
            return new MethodAccessor($reflection->name, $property, $getter, $setter);
        }

        if ($getter !== null) {
            if ($getter instanceof PropertyAccessorInterface) {
                $reader = $getter;
            } else {
                $reader = new MethodAccessor($reflection->name, $property, $getter);
            }
        } else {
            $reader = static::getPropertyAccessor($reflection, $property);
        }

        if ($readOnly) {
            return $reader;
        }

        if ($setter !== null) {
            if ($setter instanceof PropertyAccessorInterface) {
                $writter = $setter;
            } else {
                $writter = new MethodAccessor($reflection->name, $property, null, $setter);
            }
        } else {
            $writter = static::getPropertyAccessor($reflection, $property);
        }

        return new DelegateAccessor($reader, $writter);
    }

    /**
     * Guess which property accessor suit the property
     *
     * @param ReflectionClass $reflection
     * @param string $property
     *
     * @return PropertyAccessorInterface
     *
     * @throws \ReflectionException
     */
    public static function getPropertyAccessor(ReflectionClass $reflection, string $property): PropertyAccessorInterface
    {
        $reflection = self::getPropertyOwner($reflection, $property);
        $propertyReflection = $reflection->getProperty($property);

        // use reflection accessor if not set. Guess if property is public to use tue public accessor
        // if the property is readonly, reflection or closure must be used for write
        if ($propertyReflection->isPublic() && (!method_exists($propertyReflection, 'isReadOnly') || !$propertyReflection->isReadOnly())) {
            $propertyAccessor = new PublicAccessor($reflection->name, $property);
        } elseif (self::$useClosure) {
            $propertyAccessor = new ClosureAccessor($reflection->name, $property);
        } else {
            $propertyAccessor = new ReflectionAccessor($reflection->name, $property);
        }

        // In php >= 7.4 Use typed property reflection only if the property is typed to manage undefined state of the property
        if (PHP_VERSION_ID >= 70400 && $propertyReflection->hasType()) {
            return new TypedPropertyAccessor($propertyAccessor, $reflection->name, $property);
        }

        return $propertyAccessor;
    }

    /**
     * Try to guess the setter method
     *
     * @param class-string $class
     * @param string $property
     *
     * @return null|string
     *
     * @todo Manage magic method __set
     */
    public static function guessSetter(string $class, string $property): ?string
    {
        $method = 'set'.ucfirst($property);

        if (method_exists($class, $method)) {
            return $method;
        }

        return null;
    }

    /**
     * Try to guess the getter method
     *
     * @param class-string $class
     * @param string $property
     *
     * @return null|string
     *
     * @todo Manage magic method __get
     */
    public static function guessGetter(string $class, string $property): ?string
    {
        if (method_exists($class, $property)) {
            return $property;
        }

        $method = 'get'.ucfirst($property);

        if (method_exists($class, $method)) {
            return $method;
        }

        return null;
    }

    /**
     * Enable/disable the closure accessor
     *
     * @param bool $flag
     */
    public static function useClosureAccessor($flag): void
    {
        self::$useClosure = $flag;
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
