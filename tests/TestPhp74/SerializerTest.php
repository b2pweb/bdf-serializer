<?php

namespace Bdf\Serializer\TestPhp74;

use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\PropertyAccessor\Exception\AccessorException;
use Bdf\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class SerializerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        include_once __DIR__.'/Fixtures/entities.php';
    }

    /**
     *
     */
    public function test_normalize_add_null_value_for_typed_properties()
    {
        $serializer = SerializerBuilder::create()->build();
        $object = new PrivateAttribute(1, 'Foo');
        $object->setAge(null);
        $data = [
            'id' => 1,
            'firstName' => 'Foo',
            'age' => null,
        ];

        $this->assertEquals($data, $serializer->toArray($object));
        $this->assertEquals($data, $serializer->toArray($object, [NormalizationContext::NULL => true]));
    }

    /**
     *
     */
    public function test_remove_default_value_when_equals_to_null()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new Bar();
        $object->label = null;
        $this->assertEquals(['label' => null], $serializer->toArray($object, [NormalizationContext::REMOVE_DEFAULT_VALUE => true]));

        $object = new BarWithDefaultValue();
        $object->label = null;
        $this->assertEquals(['label' => null], $serializer->toArray($object));
        $this->assertEquals([], $serializer->toArray($object, [NormalizationContext::REMOVE_DEFAULT_VALUE => true]));
    }

    /**
     * @dataProvider getEntityClasses
     */
    public function test_normalize($class)
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'firstName' => 'Foo',
        ];
        $object = new $class(1, 'Foo');

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     * @dataProvider getEntityClasses
     */
    public function test_denormalize($class)
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'firstName' => 'Foo',
        ];
        $object = new $class(1, 'Foo');

        $this->assertEquals($object, $serializer->fromArray($data, $class));
    }

    /**
     * @dataProvider getEntityClasses
     */
    public function test_denormalize_null_value($class)
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'firstName' => 'Foo',
            'age' => null,
            'lastName' => null,
        ];
        $object = new $class(1, 'Foo');
        $object->setAge(null);

        $this->assertEquals($object, $serializer->fromArray($data, $class));
    }

    public function getEntityClasses()
    {
        return [
            [PrivateAttribute::class],
            [PublicAttribute::class],
        ];
    }

    /**
     *
     */
    public function test_normalize_with_error()
    {
        $this->expectException(AccessorException::class);

        $serializer = SerializerBuilder::create()->build();
        $object = new PrivateAttribute(1, 'Foo');

        $serializer->toArray($object, [NormalizationContext::THROWS_ON_ACCESSOR_ERROR => true]);
    }

    /**
     *
     */
    public function test_denormalize_with_error()
    {
        $this->expectException(AccessorException::class);

        $serializer = SerializerBuilder::create()->build();
        $data = [
            'lastName' => null,
        ];

        $serializer->fromArray($data, PrivateAttribute::class, [NormalizationContext::THROWS_ON_ACCESSOR_ERROR => true]);
    }

    /**
     *
     */
    public function test_denormalize_undefined_embeded_property()
    {
        $serializer = SerializerBuilder::create()->build();
        $data = [
            'bar' => ['label' => 'cow'],
        ];

        /** @var Foo $foo */
        $foo = $serializer->fromArray($data, Foo::class);

        $this->assertSame('cow', $foo->bar->label);
    }
}