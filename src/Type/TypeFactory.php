<?php

namespace Bdf\Serializer\Type;

/**
 * TypeFactory
 */
class TypeFactory
{
    /**
     * Alias of PHP builtin types gets by gettype.
     *
     * @var string[]
     */
    private static $mapping = [
        'object'        => \stdClass::class,
        'NULL'          => Type::TNULL,
        'unknown type'  => Type::MIXED,
//        'callback'  => 'callable',
    ];

    /**
     * The buildin types
     *
     * @var array
     */
    private static $builtin = [
        Type::STRING      => true,
        Type::INTEGER     => true,
        Type::FLOAT       => true,
        Type::DOUBLE      => true,
        Type::BOOLEAN     => true,
        Type::TNULL       => true,
        Type::TARRAY      => true,
        'int'             => true,
        'bool'            => true,
        'list'            => true,
    ];

    /**
     * Creates a type string.
     *
     * @param string|object $type
     * @psalm-param class-string<T>|T $type
     *
     * @return (T is object ? Type<T> : Type<mixed>)
     *
     * @template T is object|string
     */
    public static function createType($type): Type
    {
        $collectionType = null;
        $collection = false;

        if (is_object($type)) {
            return new Type(get_class($type), false, false, null, $type);
        }

        // Cannot guess
        if (!$type || Type::MIXED === $type) {
            return self::mixedType();
        }

        if ('[]' === substr($type, -2)) { // Type[] syntax
            $collectionType = substr($type, 0, -2);
            $type = Type::TARRAY;
            $collection = true;
        } elseif ($type === Type::TARRAY || $type === 'list') { // array
            $collectionType = Type::MIXED;
            $collection = true;
        } elseif (($pos = strpos($type, '<')) !== false && $type[strlen($type) - 1] === '>') { // Type<SubType> syntax
            $collectionType = substr($type, $pos + 1, -1);
            $type = substr($type, 0, $pos);
            $collection = $type === 'list' || $type === Type::TARRAY;
        }

        if ($collectionType) {
            $collectionType = self::createType($collectionType);
        } else {
            $collectionType = null; // Handle possible empty collection type name
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        return new Type($type, self::isBuildinType($type), $collection, $collectionType);
    }

    /**
     * Creates a type from a value.
     *
     * @param T $value
     *
     * @return Type<T>
     *
     * @template T
     */
    public static function fromValue($value): Type
    {
        $type = self::normalizeType(gettype($value));

        return static::createType($type);
    }

    /**
     * Check whether the type is a php type.
     *
     * @param string $type
     *
     * @return boolean
     */
    public static function isBuildinType($type): bool
    {
        return isset(self::$builtin[$type]);
    }

    /**
     * Get the mixed type
     *
     * @return Type<mixed>
     */
    public static function mixedType(): Type
    {
        return new Type(Type::MIXED, true);
    }

    /**
     * Normalizes the type.
     *
     * @param string $type
     *
     * @return string
     */
    private static function normalizeType($type): string
    {
        if (isset(self::$mapping[$type])) {
            return self::$mapping[$type];
        }

        return $type;
    }
}
