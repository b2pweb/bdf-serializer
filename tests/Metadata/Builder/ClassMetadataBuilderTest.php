<?php

namespace Bdf\Serializer\Metadata\Builder;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\PropertyAccessor\DelegateAccessor;
use Bdf\Serializer\PropertyAccessor\MethodAccessor;
use Bdf\Serializer\PropertyAccessor\ReflectionAccessor;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Metadata
 * @group Bdf_Serializer_Metadata_Builder
 */
class ClassMetadataBuilderTest extends TestCase
{
    /**
     *
     */
    public function test_basic_build()
    {
        $builder = $this->classBuilder(Customer::class);
        $metadata = $builder->build();

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(Customer::class, $metadata->name());
    }

    /**
     *
     */
    public function test_post_denormalization()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->postDenormalization('method');
        $metadata = $builder->build();

        $this->assertSame('method', $metadata->postDenormalization);
    }

    /**
     *
     */
    public function test_property_builder()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->string('id');
        $builder->string('name');
        $metadata = $builder->build();

        $this->assertInstanceOf(PropertyMetadata::class, $metadata->property('id'));
        $this->assertInstanceOf(PropertyMetadata::class, $metadata->property('name'));
    }

    /**
     * @dataProvider methodProvider
     */
    public function test_property_type($method, $assert)
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->$method('id');
        $metadata = $builder->build();

        $this->assertEquals($assert, $metadata->property('id')->type()->name());
    }

    public function methodProvider()
    {
        return [
            ['collection', Type::TARRAY],
            ['integer', Type::INTEGER],
            ['string', Type::STRING],
            ['dateTime', \DateTime::class],
            ['dateTimeImmutable', \DateTimeImmutable::class],
            ['boolean', Type::BOOLEAN],
            ['float', Type::FLOAT],
            ['object', \stdClass::class],
            ['null', Type::TNULL],
            ['mixed', Type::MIXED],
        ];
    }

    /**
     *
     */
    public function test_use_getters()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->string('id');
        $builder->useGetters();
        $metadata = $builder->build();

        $accessor = $metadata->property('id')->accessor();

        $this->assertInstanceOf(DelegateAccessor::class, $accessor);
        $this->assertInstanceOf(MethodAccessor::class, $accessor->getReader());
        $this->assertInstanceOf(ReflectionAccessor::class, $accessor->getWriter());
    }

    /**
     *
     */
    public function test_use_setters()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->string('id');
        $builder->useSetters();
        $metadata = $builder->build();

        $accessor = $metadata->property('id')->accessor();

        $this->assertInstanceOf(DelegateAccessor::class, $accessor);
        $this->assertInstanceOf(ReflectionAccessor::class, $accessor->getReader());
        $this->assertInstanceOf(MethodAccessor::class, $accessor->getWriter());
    }

    /**
     *
     */
    public function test_use_setters_and_getters()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->string('id');
        $builder->useSetters();
        $builder->useGetters();
        $metadata = $builder->build();

        $accessor = $metadata->property('id')->accessor();

        $this->assertInstanceOf(MethodAccessor::class, $accessor);
    }

    /**
     *
     */
    public function test_use_setters_and_getters_when_method_do_not_exist()
    {
        $builder = $this->classBuilder(Customer::class);
        $builder->string('name');
        $builder->useSetters();
        $builder->useGetters();
        $metadata = $builder->build();

        $accessor = $metadata->property('name')->accessor();

        $this->assertInstanceOf(ReflectionAccessor::class, $accessor);
    }

    /**
     * @param string $class
     *
     * @return ClassMetadataBuilder
     */
    private function classBuilder($class)
    {
        $reflection = new \ReflectionClass($class);

        return new ClassMetadataBuilder($reflection);
    }
}

//---------------------

class Customer
{
    protected $id;
    protected $name;
    public $contact;
    public $date;

    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }
}