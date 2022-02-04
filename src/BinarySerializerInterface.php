<?php

namespace Bdf\Serializer;

/**
 * Serializer using binary representation
 * Better performance and payload but not human readable and portable
 *
 * With igbinary :
 * - Unserialize performance increased by ~20%
 * - Serialized string is 10-15% smaller
 * - No impact no serialize speed (can be slightly slower or faster)
 *
 * @uses igbinary
 * @link https://github.com/igbinary/igbinary
 */
interface BinarySerializerInterface
{
    /**
     * Serialize data to binary
     *
     * @param mixed $data
     * @param array $context
     *
     * @return string
     */
    public function toBinary($data, array $context = []);

    /**
     * Restores objects from binary.
     *
     * @param string $raw
     * @param class-string<T>|T $type  The type of data. Can be the target object.
     *
     * @return T|T[] this returns whatever the passed type is, typically an object or an array of objects
     *
     * @template T
     */
    public function fromBinary(string $raw, $type);
}
