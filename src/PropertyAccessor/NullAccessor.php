<?php

namespace Bdf\Serializer\PropertyAccessor;

/**
 * NullAccessor
 */
class NullAccessor implements PropertyAccessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        return null;
    }
}
