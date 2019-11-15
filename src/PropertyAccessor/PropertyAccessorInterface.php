<?php

namespace Bdf\Serializer\PropertyAccessor;

/**
 * PropertyAccessorInterface
 */
interface PropertyAccessorInterface
{
    /**
     * Write value on the object property
     *
     * @param object $object
     * @param mixed  $value
     */
    public function write($object, $value);

    /**
     * Get the property value of the object
     *
     * @param object $object
     *
     * @return mixed
     */
    public function read($object);
}
