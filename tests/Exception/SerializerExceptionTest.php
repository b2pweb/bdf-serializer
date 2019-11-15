<?php

namespace Bdf\Serializer\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Exception
 */
class SerializerExceptionTest extends TestCase
{
    /**
     *
     */
    public function test_serialize()
    {
        $exception = new UnexpectedValueException();

        $this->assertInstanceOf(SerializerException::class, $exception);
    }
}