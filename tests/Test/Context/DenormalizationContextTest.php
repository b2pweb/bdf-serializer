<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Context
 */
class DenormalizationContextTest extends TestCase
{
    /**
     *
     */
    public function test_duplicate_without_options()
    {
        $context = $this->context([
            'serializeNull' => true,
        ]);

        $newContext = $context->duplicate();

        $this->assertSame($newContext, $context);
    }

    /**
     *
     */
    public function test_duplicate_with_options()
    {
        $context = $this->context([
            'foo' => 'all',
            'bar' => true,
        ]);

        $newContext = $context->duplicate([
            'serializeNull' => false,
        ]);

        $this->assertSame('all', $newContext->option('foo'));
        $this->assertSame(true, $newContext->option('bar'));
    }

    /**
     *
     */
    private function serializer()
    {
        return $this->createMock(Serializer::class);
    }

    /**
     *
     */
    private function context($options = null)
    {
        return new DenormalizationContext($this->serializer(), $options);
    }
}
