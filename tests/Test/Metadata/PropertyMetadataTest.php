<?php

namespace Bdf\Serializer\Metadata;

use Bdf\Serializer\PropertyAccessor\PropertyAccessorInterface;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Metadata
 */
class PropertyMetadataTest extends TestCase
{
    /**
     *
     */
    public function test_default_getter()
    {
        $metadata = $this->getMetadata();

        $this->assertSame('attribute', $metadata->name());
        $this->assertSame('Class', $metadata->className());
        $this->assertSame([], $metadata->groups());
        $this->assertSame(null, $metadata->type());
        $this->assertSame(null, $metadata->alias());
        $this->assertSame(null, $metadata->accessor());
        $this->assertSame(null, $metadata->since());
        $this->assertSame(null, $metadata->until());
        $this->assertSame(false, $metadata->readOnly());
        $this->assertSame(false, $metadata->inline());
        $this->assertSame(null, $metadata->defaultValue());
    }

    /**
     *
     */
    public function test_get_set_alias()
    {
        $metadata = $this->getMetadata();
        $metadata->setAlias('my_alias');

        $this->assertEquals('my_alias', $metadata->alias());
    }

    /**
     *
     */
    public function test_get_set_type()
    {
        $metadata = $this->getMetadata();
        $metadata->setType(new Type(Type::INTEGER, true));

        $this->assertInstanceOf(Type::class, $metadata->type());
        $this->assertEquals(Type::INTEGER, $metadata->type()->name());
    }

    /**
     *
     */
    public function test_has_set_groups()
    {
        $metadata = $this->getMetadata();
        $metadata->setGroups(['all', 'id']);

        $this->assertTrue($metadata->hasGroups(['all']));
        $this->assertTrue($metadata->hasGroups(['all', 'other']));
        $this->assertFalse($metadata->hasGroups(['other']));
    }

    /**
     *
     */
    public function test_get_set_accessor()
    {
        $accessor = $this->createMock(PropertyAccessorInterface::class);

        $metadata = $this->getMetadata();
        $metadata->setAccessor($accessor);

        $this->assertEquals($accessor, $metadata->accessor());
    }

    /**
     *
     */
    public function test_get_set_since_version()
    {
        $metadata = $this->getMetadata();
        $metadata->setSince('1.0');

        $this->assertEquals('1.0', $metadata->since());
    }

    /**
     *
     */
    public function test_get_set_until_version()
    {
        $metadata = $this->getMetadata();
        $metadata->setUntil('1.0');

        $this->assertEquals('1.0', $metadata->until());
    }

    /**
     *
     */
    public function test_match_version()
    {
        $metadata = $this->getMetadata();
        $metadata->setSince('1.0.0');
        $metadata->setUntil('2.0.0');

        $this->assertFalse($metadata->matchVersion('0.1.2'));
        $this->assertTrue($metadata->matchVersion('1.0.0'));
        $this->assertTrue($metadata->matchVersion('1.1.2'));
        $this->assertTrue($metadata->matchVersion('2.0.0'));
        $this->assertFalse($metadata->matchVersion('2.1.0'));
    }

    /**
     *
     */
    public function test_get_set_read_only()
    {
        $metadata = $this->getMetadata();
        $metadata->setReadOnly(true);

        $this->assertTrue($metadata->readOnly());
    }

    /**
     *
     */
    public function test_get_set_inline()
    {
        $metadata = $this->getMetadata();
        $metadata->setInline(true);

        $this->assertTrue($metadata->inline());
    }

    /**
     *
     */
    public function test_set_get_normalization_options()
    {
        $metadata = $this->getMetadata();
        $metadata->setNormalizationOptions(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $metadata->normalizationOptions());
    }

    /**
     *
     */
    public function test_set_get_denormalization_options()
    {
        $metadata = $this->getMetadata();
        $metadata->setDenormalizationOptions(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $metadata->denormalizationOptions());
    }

    /**
     *
     */
    public function test_get_set_default_value()
    {
        $metadata = $this->getMetadata();
        $metadata->setDefaultValue('default');

        $this->assertEquals('default', $metadata->defaultValue());
    }

    /**
     * @return PropertyMetadata
     */
    private function getMetadata()
    {
        return new PropertyMetadata('Class', 'attribute');
    }
}