<?php

namespace Bdf\Serializer\PropertyAccessor\Exception;

use Bdf\Serializer\Exception\SerializerException;

/**
 * This exception is thrown if accessor gets the Php "Error" on read or write.
 */
class AccessorException extends \UnexpectedValueException implements SerializerException
{
}
