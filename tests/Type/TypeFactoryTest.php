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

        $this->assertEquals(Type::STRING, $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_builtin()
    {
        $type = TypeFactory::createType('string[]');

        $this->assertEquals(Type::TARRAY, $type->name());
        $this->assertEquals(true, $type->isArray());
        $this->assertEquals(Type::STRING, $type->subType()->name());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_className()
    {
        $type = TypeFactory::createType('Customer');

        $this->assertEquals('Customer', $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(false, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_className()
    {
        $type = TypeFactory::createType('Customer[]');

        $this->assertEquals(Type::TARRAY, $type->name());
        $this->assertEquals(true, $type->isArray());
        $this->assertEquals('Customer', $type->subType()->name());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_unknown()
    {
        $type = TypeFactory::createType('mixed');

        $this->assertEquals(Type::MIXED, $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_collection_unknown()
    {
        $type = TypeFactory::createType('mixed[]');

        $this->assertEquals(Type::TARRAY, $type->name());
        $this->assertEquals(true, $type->isArray());
        $this->assertEquals(Type::MIXED, $type->subType()->name());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_array()
    {
        $type = TypeFactory::createType(Type::TARRAY);

        $this->assertEquals(Type::TARRAY, $type->name());
        $this->assertEquals(true, $type->isArray());
        $this->assertEquals(Type::MIXED, $type->subType()->name());
        $this->assertEquals(true, $type->isBuildin());
    }

    /**
     *
     */
    public function test_object()
    {
        $type = TypeFactory::createType(\stdClass::class);

        $this->assertEquals(\stdClass::class, $type->name());
        $this->assertEquals(false, $type->isArray());
        $this->assertEquals(null, $type->subType());
        $this->assertEquals(false, $type->isBuildin());
    }

    /**
     *
     */
    public function test_from_value()
    {
        $this->assertEquals(\stdClass::class, TypeFactory::fromValue(new \stdClass())->name());
        $this->assertEquals(Type::TNULL, TypeFactory::fromValue(null)->name());
    }

    /**
     *
     */
    public function test_parametrized_type()
    {
        $type = TypeFactory::createType('MyType<SubType>');

        $this->assertTrue($type->isParametrized());
        $this->assertFalse($type->isArray());
        $this->assertEquals('MyType', $type->name());
        $this->assertEquals('SubType', $type->subType()->name());
    }

    /**
     *
     */
    public function test_complex_type()
    {
        $type = TypeFactory::createType('Wrapper<MyType<SubType>>[]');

        $this->assertTrue($type->isArray());
        $this->assertEquals('Wrapper', $type->subType()->name());
        $this->assertFalse($type->subType()->isArray());
        $this->assertEquals('MyType', $type->subType()->subType()->name());
        $this->assertFalse($type->subType()->subType()->isArray());
        $this->assertEquals('SubType', $type->subType()->subType()->subType()->name());
        $this->assertFalse($type->subType()->subType()->subType()->isArray());
    }
}
