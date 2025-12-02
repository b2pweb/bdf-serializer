<?php

namespace Bdf\Serializer\Type;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Type
 */
class TypeFactoryTest extends TestCase
{
    /**
     *
     */
    public function test_builtin_type()
    {
        $type = TypeFactory::createType(Type::STRING);

        $this->assertSame(Type::STRING, $type->name());
        $this->assertSame(false, $type->isArray());
        $this->assertSame(null, $type->subType());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_builtin()
    {
        $type = TypeFactory::createType('string[]');

        $this->assertSame(Type::TARRAY, $type->name());
        $this->assertSame(true, $type->isArray());
        $this->assertSame(Type::STRING, $type->subType()->name());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_className()
    {
        $type = TypeFactory::createType('Customer');

        $this->assertSame('Customer', $type->name());
        $this->assertSame(false, $type->isArray());
        $this->assertSame(null, $type->subType());
        $this->assertSame(false, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_className()
    {
        $type = TypeFactory::createType('Customer[]');

        $this->assertSame(Type::TARRAY, $type->name());
        $this->assertSame(true, $type->isArray());
        $this->assertSame('Customer', $type->subType()->name());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_unknown()
    {
        $type = TypeFactory::createType('mixed');

        $this->assertSame(Type::MIXED, $type->name());
        $this->assertSame(false, $type->isArray());
        $this->assertSame(null, $type->subType());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_unknown()
    {
        $type = TypeFactory::createType('mixed[]');

        $this->assertSame(Type::TARRAY, $type->name());
        $this->assertSame(true, $type->isArray());
        $this->assertSame(Type::MIXED, $type->subType()->name());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_array()
    {
        $type = TypeFactory::createType(Type::TARRAY);

        $this->assertSame(Type::TARRAY, $type->name());
        $this->assertSame(true, $type->isArray());
        $this->assertSame(Type::MIXED, $type->subType()->name());
        $this->assertSame(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_object()
    {
        $type = TypeFactory::createType(\stdClass::class);

        $this->assertSame(\stdClass::class, $type->name());
        $this->assertSame(false, $type->isArray());
        $this->assertSame(null, $type->subType());
        $this->assertSame(false, $type->isBuildin());
    }

    /**
     *
     */
    public function test_from_value()
    {
        $this->assertSame(\stdClass::class, TypeFactory::fromValue(new \stdClass())->name());
        $this->assertSame(Type::TNULL, TypeFactory::fromValue(null)->name());
    }

    /**
     *
     */
    public function test_parametrized_type()
    {
        $type = TypeFactory::createType('MyType<SubType>');

        $this->assertTrue($type->isParametrized());
        $this->assertFalse($type->isArray());
        $this->assertSame('MyType', $type->name());
        $this->assertSame('SubType', $type->subType()->name());
    }

    /**
     *
     */
    public function test_parametrized_array()
    {
        $type = TypeFactory::createType('array<SubType>');

        $this->assertTrue($type->isParametrized());
        $this->assertTrue($type->isArray());
        $this->assertSame('array', $type->name());
        $this->assertSame('SubType', $type->subType()->name());
    }

    /**
     *
     */
    public function test_parametrized_list()
    {
        $type = TypeFactory::createType('list<SubType>');

        $this->assertTrue($type->isParametrized());
        $this->assertTrue($type->isArray());
        $this->assertSame('list', $type->name());
        $this->assertSame('SubType', $type->subType()->name());
    }

    /**
     *
     */
    public function test_complex_type()
    {
        $type = TypeFactory::createType('Wrapper<MyType<SubType>>[]');

        $this->assertTrue($type->isArray());
        $this->assertSame('Wrapper', $type->subType()->name());
        $this->assertFalse($type->subType()->isArray());
        $this->assertSame('MyType', $type->subType()->subType()->name());
        $this->assertFalse($type->subType()->subType()->isArray());
        $this->assertSame('SubType', $type->subType()->subType()->subType()->name());
        $this->assertFalse($type->subType()->subType()->subType()->isArray());
    }

    /**
     *
     */
    public function test_int_type()
    {
        $type = TypeFactory::createType('int');

        // NOTE should we map to integer ?
        $this->assertEquals('int', $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(true, $type->isBuildin());
    }
}
