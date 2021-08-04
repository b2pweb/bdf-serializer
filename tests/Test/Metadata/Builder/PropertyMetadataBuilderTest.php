<?php

namespace Bdf\Serializer\Metadata\Builder;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\PropertyAccessor\DelegateAccessor;
use Bdf\Serializer\PropertyAccessor\MethodAccessor;
use Bdf\Serializer\PropertyAccessor\PublicAccessor;
use Bdf\Serializer\PropertyAccessor\ReflectionAccessor;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Metadata
 * @group Bdf_Serializer_Metadata_Builder
 */
class PropertyMetadataBuilderTest extends TestCase
{
    /**
     *
     */
    public function test_basic_build()
    {
        $builder = $this->propertyBuilder(User::class);
        $metadata = $builder->build();

        $this->assertInstanceOf(PropertyMetadata::class, $metadata);
        $this->assertEquals(User::class, $metadata->className());
        $this->assertEquals('id', $metadata->name());
        $this->assertEquals('id', $metadata->alias());
    }

    /**
     *
     */
    public function test_alias()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->alias('Foo');
        $metadata = $builder->build();

        $this->assertEquals('id', $metadata->name());
        $this->assertEquals('Foo', $metadata->alias());
    }

    /**
     *
     */
    public function test_property_type()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->type(Type::INTEGER);
        $metadata = $builder->build();

        $this->assertEquals(Type::INTEGER, $metadata->type()->name());
    }

    /**
     *
     */
    public function test_as_collection()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->type(Type::INTEGER)->collection();
        $metadata = $builder->build();

        $this->assertEquals(Type::TARRAY, $metadata->type()->name());
        $this->assertEquals(Type::INTEGER, $metadata->type()->subType()->name());
        $this->assertTrue($metadata->type()->isArray());
    }

    /**
     *
     */
    public function test_collection_of()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->collectionOf(Type::INTEGER);
        $metadata = $builder->build();

        $this->assertEquals(Type::TARRAY, $metadata->type()->name());
        $this->assertEquals(Type::INTEGER, $metadata->type()->subType()->name());
        $this->assertTrue($metadata->type()->isArray());
    }

    /**
     *
     */
    public function test_wrapper_of()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->type(\ArrayObject::class);
        $builder->wrapperOf(Type::INTEGER);
        $metadata = $builder->build();

        $this->assertEquals(\ArrayObject::class, $metadata->type()->name());
        $this->assertEquals(Type::INTEGER, $metadata->type()->subType()->name());
        $this->assertFalse($metadata->type()->isArray());
    }

    /**
     *
     */
    public function test_multi_dimension_collection()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->collectionOf(Type::INTEGER)->collection();
        $metadata = $builder->build();

        $this->assertEquals(Type::TARRAY, $metadata->type()->name());
        $this->assertEquals(Type::TARRAY, $metadata->type()->subType()->name());
        $this->assertEquals(Type::INTEGER, $metadata->type()->subType()->subType()->name());
        $this->assertTrue($metadata->type()->isArray());
    }

    /**
     *
     */
    public function test_property_group()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->groups(['identifier']);
        $metadata = $builder->build();

        $this->assertFalse($metadata->hasGroups(['all']));
        $this->assertTrue($metadata->hasGroups(['identifier']));

        $builder->addGroup('all');
        $metadata = $builder->build();
        $this->assertTrue($metadata->hasGroups(['all']));
        $this->assertTrue($metadata->hasGroups(['identifier']));
    }

    /**
     *
     */
    public function test_property_default_accesor()
    {
        $metadata = $this->propertyBuilder(User::class)->build();
        $this->assertInstanceOf(ReflectionAccessor::class, $metadata->accessor());

        $metadata = $this->propertyBuilder(User::class, 'name')->build();
        $this->assertInstanceOf(PublicAccessor::class, $metadata->accessor());
    }

    /**
     *
     */
    public function test_reader_accesor()
    {
        $metadata = $this->propertyBuilder(User::class, 'name')
            ->readWith('getName')
            ->build();

        $accessor = $metadata->accessor();
        $this->assertInstanceOf(DelegateAccessor::class, $accessor);
        $this->assertInstanceOf(MethodAccessor::class, $accessor->getReader());
        $this->assertInstanceOf(PublicAccessor::class, $accessor->getWriter());
    }

    /**
     *
     */
    public function test_writer_accesor()
    {
        $metadata = $this->propertyBuilder(User::class, 'name')
            ->writeWith('setName')
            ->build();

        $accessor = $metadata->accessor();
        $this->assertInstanceOf(DelegateAccessor::class, $accessor);
        $this->assertInstanceOf(PublicAccessor::class, $accessor->getReader());
        $this->assertInstanceOf(MethodAccessor::class, $accessor->getWriter());
    }

    /**
     *
     */
    public function test_reader_and_writer_accesor()
    {
        $metadata = $this->propertyBuilder(User::class, 'name')
            ->readWith('getName')
            ->writeWith('setName')
            ->build();

        $accessor = $metadata->accessor();
        $this->assertInstanceOf(MethodAccessor::class, $accessor);
    }

    /**
     *
     */
    public function test_since()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->since('1.0');
        $metadata = $builder->build();

        $this->assertSame('1.0', $metadata->since());
    }

    /**
     *
     */
    public function test_until()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->until('1.0');
        $metadata = $builder->build();

        $this->assertSame('1.0', $metadata->until());
    }

    /**
     *
     */
    public function test_read_only()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->readOnly();
        $metadata = $builder->build();

        $this->assertTrue($metadata->readOnly());
    }

    /**
     *
     */
    public function test_inline()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->inline();
        $metadata = $builder->build();

        $this->assertTrue($metadata->inline());
    }

    /**
     *
     */
    public function test_normalization()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->normalization('foo', 'bar');
        $metadata = $builder->build();

        $this->assertSame('bar', $metadata->normalizationOptions['foo']);
    }

    /**
     *
     */
    public function test_denormalization()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->denormalization('foo', 'bar');
        $metadata = $builder->build();

        $this->assertSame('bar', $metadata->denormalizationOptions['foo']);
    }

    /**
     *
     */
    public function test_date_format()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->dateFormat('d/m/Y');
        $metadata = $builder->build();

        $this->assertSame('d/m/Y', $metadata->normalizationOptions[NormalizationContext::DATETIME_FORMAT]);
        $this->assertSame('d/m/Y', $metadata->denormalizationOptions[DenormalizationContext::DATETIME_FORMAT]);
    }

    /**
     *
     */
    public function test_timezone()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->timezone('+02:00');
        $metadata = $builder->build();

        $this->assertSame('+02:00', $metadata->denormalizationOptions[DenormalizationContext::TIMEZONE]);
    }

    /**
     *
     */
    public function test_to_timezone()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->toTimezone('+00:00');
        $metadata = $builder->build();

        $this->assertSame('+00:00', $metadata->normalizationOptions[NormalizationContext::TIMEZONE]);
        $this->assertSame('+00:00', $metadata->denormalizationOptions[DenormalizationContext::TIMEZONE_HINT]);
    }

    /**
     *
     */
    public function test_conserveNull()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->conserveNull();
        $metadata = $builder->build();

        $this->assertTrue($metadata->normalizationOptions[NormalizationContext::NULL]);
    }

    /**
     *
     */
    public function test_conserve_default()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->conserveDefault();
        $metadata = $builder->build();

        $this->assertFalse($metadata->normalizationOptions[NormalizationContext::REMOVE_DEFAULT_VALUE]);
    }

    /**
     *
     */
    public function test_load_default()
    {
        $builder = $this->propertyBuilder(UserWithDefaultValues::class);
        $metadata = $builder->build();

        $this->assertSame(0, $metadata->defaultValue);
    }

    /**
     *
     */
    public function test_virtual()
    {
        $builder = $this->propertyBuilder(User::class);
        $builder->virtual('virtualName');

        $metadata = $builder->build();

        $this->assertTrue($metadata->readOnly);
        $this->assertSame('foo', $metadata->accessor->read(new User()));
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return PropertyMetadataBuilder
     */
    private function propertyBuilder($class, $property = 'id')
    {
        $reflection = new \ReflectionClass($class);

        return new PropertyMetadataBuilder($reflection, $property);
    }
}

//---------------------

class User
{
    protected $id;
    public $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function virtualName()
    {
        return 'foo';
    }
}
class UserWithDefaultValues
{
    public $id = 0;
}