<?php

namespace Bdf\Serializer\Type;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Type
 */
class TypeTest extends TestCase
{
    /**
     *
     */
    public function test_default_getter()
    {
        $type = new Type('string', true);

        $this->assertEquals('string', $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(true, $type->isBuildin());
        $this->assertEquals(null, $type->target());
    }

    /**
     *
     */
    public function test_set_get_target()
    {
        $type = new Type('string', true);
        $type->setTarget('target');

        $this->assertEquals('target', $type->target());
    }

    /**
     *
     */
    public function test_convert_to_string()
    {
        $type = new Type('string', true);

        $this->assertSame('123', $type->convert(123));
        $this->assertSame('', $type->convert(null));
        $this->assertSame('1', $type->convert(true));
        $this->assertSame('123', $type->convert('123'));
        $this->assertSame('12.3', $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_int()
    {
        $type = new Type('integer', true);

        $this->assertSame(123, $type->convert(123));
        $this->assertSame(0, $type->convert(null));
        $this->assertSame(1, $type->convert(true));
        $this->assertSame(123, $type->convert('123'));
        $this->assertSame(12, $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_float()
    {
        $type = new Type('float', true);

        $this->assertSame(123.0, $type->convert(123));
        $this->assertSame(.0, $type->convert(null));
        $this->assertSame(1.0, $type->convert(true));
        $this->assertSame(123.0, $type->convert('123'));
        $this->assertSame(12.3, $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_array()
    {
        $type = new Type('array', true);

        $this->assertSame([123], $type->convert(123));
        $this->assertSame([], $type->convert(null));
        $this->assertSame([true], $type->convert(true));
        $this->assertSame(['123'], $type->convert('123'));
        $this->assertSame([12.3], $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_bool()
    {
        $type = new Type('boolean', true);

        $this->assertSame(true, $type->convert(123));
        $this->assertSame(false, $type->convert(null));
        $this->assertSame(true, $type->convert(true));
        $this->assertSame(true, $type->convert('123'));
        $this->assertSame(true, $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_null()
    {
        $type = new Type('null', true);

        $this->assertSame(null, $type->convert(123));
        $this->assertSame(null, $type->convert(null));
        $this->assertSame(null, $type->convert(true));
        $this->assertSame(null, $type->convert('123'));
        $this->assertSame(null, $type->convert(12.3));
    }

    /**
     *
     */
    public function test_convert_to_unknown_type()
    {
        $type = new Type('unknown', true);

        $this->assertSame(123, $type->convert(123));
        $this->assertSame(null, $type->convert(null));
        $this->assertSame(true, $type->convert(true));
        $this->assertSame('123', $type->convert('123'));
        $this->assertSame(12.3, $type->convert(12.3));
    }

    /**
     *
     */
    public function test_serialization()
    {
        $target = function() {};
        $type = new Type('string', true, false, null, $target);

        $this->assertSame($target, $type->target());

        $serialized = serialize($type);
        $type = unserialize($serialized);

        $this->assertNull($type->target());
    }

    /**
     *
     */
    public function test_hint_no_change()
    {
        $value = 'my value';
        $type = new Type('string', true);
        $cloned = clone $type;

        $this->assertSame($type, $type->hint($value));
        $this->assertEquals($cloned, $type);
        $this->assertEquals('my value', $value);
    }

    /**
     *
     */
    public function test_hint_with_type_metadata()
    {
        $value = [
            '@type' => \stdClass::class,
            'data' => ['foo' => 'bar'],
        ];
        $target = new \stdClass();
        $type = new Type('string', true);
        $type->setTarget($target);
        $cloned = clone $type;

        $hinted = $type->hint($value);

        $this->assertNotSame($type, $hinted);
        $this->assertEquals(new Type(\stdClass::class, false, false, null, $target), $hinted);
        $this->assertSame($target, $hinted->target());
        $this->assertEquals($cloned, $type);
        $this->assertEquals(['foo' => 'bar'], $value);
    }

    /**
     *
     */
    public function test_hint_with_mixed_type()
    {
        $value = 'foo';
        $type = new Type('mixed', true);
        $cloned = clone $type;

        $hinted = $type->hint($value);

        $this->assertNotSame($type, $hinted);
        $this->assertEquals(new Type('mixed', true), $hinted);
        $this->assertEquals($cloned, $type);
        $this->assertEquals('foo', $value);
    }

    /**
     *
     */
    public function test_hint_with_mixed_type_array()
    {
        $value = ['foo', 'bar'];
        $type = new Type('mixed', true);
        $cloned = clone $type;

        $hinted = $type->hint($value);

        $this->assertNotSame($type, $hinted);
        $this->assertEquals(new Type('mixed', true, true, TypeFactory::mixedType()), $hinted);
        $this->assertEquals($cloned, $type);
        $this->assertEquals(['foo', 'bar'], $value);
    }
}
