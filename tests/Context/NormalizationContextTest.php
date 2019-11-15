<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Exception\CircularReferenceException;
use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\Serializer;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Context
 */
class NormalizationContextTest extends TestCase
{
    /**
     *
     */
    public function test_default_options()
    {
        $context = $this->context();

        $this->assertSame(null, $context->groups());
        $this->assertSame(null, $context->version());
        $this->assertSame(null, $context->excludeProperties());
        $this->assertSame(null, $context->includeProperties());
        $this->assertSame(false, $context->shouldAddNull());
        $this->assertSame(null, $context->option('unknown'));
        $this->assertEquals($this->serializer(), $context->root());
    }

    /**
     *
     */
    public function test_options()
    {
        $options = [
            'group' => 'all',
            'version' => '1.0.0',
            'exclude' => 'id',
            'include' => 'name',
            'null' => true,
            'unknown' => 'known',
        ];

        $context = $this->context($options);

        $this->assertSame(['all'], $context->groups());
        $this->assertSame('1.0.0', $context->version());
        $this->assertSame(['id' => 0], $context->excludeProperties());
        $this->assertSame(['name' => 0], $context->includeProperties());
        $this->assertSame(true, $context->shouldAddNull());
        $this->assertSame('known', $context->option('unknown'));
    }

    /**
     *
     */
    public function test_option_aliases()
    {
        $options = [
            'groups' => 'all',
            'serializeNull' => true,
        ];

        $context = $this->context($options);

        $this->assertSame(['all'], $context->groups());
        $this->assertSame(true, $context->shouldAddNull());
    }

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
            'groups' => 'all',
            'serializeNull' => true,
        ]);

        $newContext = $context->duplicate([
            'serializeNull' => false,
        ]);

        $this->assertSame(false, $newContext->shouldAddNull());
        $this->assertSame(['all'], $newContext->groups());
    }

    /**
     *
     */
    public function test_skip_property_without_options()
    {
        $this->assertSame(false, $this->context()->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_property_in_group()
    {
        $options = [
            'groups' => 'all',
        ];

        $this->assertSame(false, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_skip_property_not_in_group()
    {
        $options = [
            'groups' => 'unused_group',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_property_not_exclude()
    {
        $options = [
            'exclude' => 'unused',
        ];

        $this->assertSame(false, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_skip_property_excluded()
    {
        $options = [
            'exclude' => 'name',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_skip_property_excluded_by_path()
    {
        $options = [
            'exclude' => 'Foo::name',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_property_included()
    {
        $options = [
            'include' => 'name',
        ];

        $this->assertSame(false, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_property_included_by_path()
    {
        $options = [
            'include' => 'Foo::name',
        ];

        $this->assertSame(false, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_skip_property_not_included()
    {
        $options = [
            'include' => 'unused',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_versionned_property_with_good_version()
    {
        $options = [
            'version' => '1.0.1',
        ];

        $this->assertSame(false, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_not_skip_unversionned_property()
    {
        $options = [
            'version' => '1.0.1',
        ];

        $property = new PropertyMetadata('Foo', 'name');
        $property->setType(new Type('string', true));

        $this->assertSame(false, $this->context($options)->skipProperty($property));
    }

    /**
     *
     */
    public function test_skip_past_version()
    {
        $options = [
            'version' => '0.0.1',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_skip_not_ranged_version()
    {
        $options = [
            'version' => '3.0.1',
        ];

        $this->assertSame(true, $this->context($options)->skipProperty($this->property()));
    }

    /**
     *
     */
    public function test_assert_no_circular_ref()
    {
        $object = new \stdClass;

        $this->assertSame(spl_object_hash($object), $this->context()->assertNoCircularReference($object));
    }

    /**
     *
     */
    public function test_assert_circular_ref()
    {
        $this->expectException(CircularReferenceException::class);

        $object = new \stdClass;
        $context = $this->context();

        $context->assertNoCircularReference($object);
        $context->assertNoCircularReference($object);
    }

    /**
     *
     */
    public function test_no_assert_circular_ref_with_limit()
    {
        $object = new \stdClass;
        $context = $this->context([NormalizationContext::CIRCULAR_REFERENCE_LIMIT => 2]);

        $hash = $context->assertNoCircularReference($object);
        $this->assertSame($hash, $context->assertNoCircularReference($object));
    }

    /**
     *
     */
    public function test_release_ref()
    {
        $object = new \stdClass;
        $context = $this->context([NormalizationContext::CIRCULAR_REFERENCE_LIMIT => 2]);

        $hash = $context->assertNoCircularReference($object);
        $context->assertNoCircularReference($object);

        $context->releaseReference($hash);
        $this->assertSame($hash, $context->assertNoCircularReference($object));

        $context->releaseReference($hash);
        $this->assertSame($hash, $context->assertNoCircularReference($object));
    }

    /**
     *
     */
    public function test_default_value()
    {
        $property = $this->property();
        $property->defaultValue = 1;

        $context = $this->context();
        $this->assertFalse($context->skipPropertyValue($property, 0));
        $this->assertTrue($context->skipPropertyValue($property, null));
        $this->assertFalse($context->skipPropertyValue($property, 1));

        $context = $this->context([NormalizationContext::REMOVE_DEFAULT_VALUE => true]);
        $this->assertFalse($context->skipPropertyValue($property, 0));
        $this->assertTrue($context->skipPropertyValue($property, null));
        $this->assertTrue($context->skipPropertyValue($property, 1));

        $context = $this->context([NormalizationContext::NULL => true]);
        $this->assertFalse($context->skipPropertyValue($property, null));
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
    private function context(array $options = [])
    {
        return new NormalizationContext($this->serializer(), $options);
    }

    /**
     *
     */
    private function property()
    {
        $property = new PropertyMetadata('Foo', 'name');
        $property->setType(new Type('string', true));
        $property->setGroups(['all', 'bar']);
        $property->setSince('1.0.0');
        $property->setUntil('2.0.0');
        return $property;
    }
}
