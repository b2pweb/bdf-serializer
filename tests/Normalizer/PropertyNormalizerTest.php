<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\CircularReferenceException;
use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use Bdf\Serializer\Metadata\MetadataFactory;
use Bdf\Serializer\SerializerBuilder;
use DateTime;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 * @group Bdf_Serializer_Normalizer_Property
 */
class PropertyNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_supports()
    {
        $normalizer = $this->getNormalizer();

        $this->assertTrue($normalizer->supports(stdClass::class));
        $this->assertFalse($normalizer->supports('unknown'));
    }

    /**
     *
     */
    public function test_normalize_date_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new EntityWithDate();
        $object->date = new DateTime('2017-06-28T12:32:26+01:00');
        $object->basicDate = new DateTime('2017-06-28T12:32:26+01:00');

        $result = $serializer->toArray($object);

        $this->assertEquals('28/06/2017 11:32:26', $result['date']);
        $this->assertEquals('2017-06-28T12:32:26+01:00', $result['basicDate']);
    }

    /**
     *
     */
    public function test_denormalize_date_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $array = ['date' => '28/06/2017 11:32:26', 'basicDate' => '2017-06-28T12:32:26+0100'];
        $expected = new DateTime('2017-06-28T12:32:26+01:00');
        $expectedBasicDate = new DateTime('2017-06-28T12:32:26+01:00');

        $object = $serializer->fromArray($array, EntityWithDate::class);

        $this->assertEquals($expected, $object->date);
        $this->assertEquals($expectedBasicDate, $object->basicDate);
        $this->assertEquals('+01:00', $object->date->getTimezone()->getName());
        $this->assertEquals('12:32:26', $object->date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_priority_on_property_options()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new EntityWithDate();
        $object->date = new DateTime('2017-06-28T12:32:26+01:00');

        $result = $serializer->toArray($object, [NormalizationContext::DATETIME_FORMAT => 'Y']);

        $this->assertNotEquals('2017', $result['date']);
    }

    /**
     *
     */
    public function test_null_property_option()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new EntityWithDate();

        $result = $serializer->toArray($object);

        $this->assertTrue(array_key_exists('date', $result));
        $this->assertNull($result['date']);
    }

    /**
     *
     */
    public function test_circular_ref()
    {
        $this->expectException(CircularReferenceException::class);

        $serializer = SerializerBuilder::create()->build();

        $object = new EntityWithCircularRef();
        $object->ref = $object;

        $serializer->toArray($object);
    }

    /**
     *
     */
    public function test_default_value_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new EntityWithValueToSkip();
        $result = $serializer->toArray($object);

        $this->assertTrue(array_key_exists('firstName', $result));
        $this->assertTrue(array_key_exists('lastName', $result));
        $this->assertSame('john', $result['firstName']);
        $this->assertSame('doe', $result['lastName']);

        $result = $serializer->toArray($object, [NormalizationContext::REMOVE_DEFAULT_VALUE => true]);

        $this->assertTrue(array_key_exists('firstName', $result));
        $this->assertFalse(array_key_exists('lastName', $result));
        $this->assertSame('john', $result['firstName']);
    }

    /**
     *
     */
    public function test_inline_property_with_complex_value()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new InlineEntity();
        $object->age = 18;
        $object->entity = new EntityWithValueToSkip();

        $result = $serializer->toArray($object);

        $this->assertSame(18, $result['age']);
        $this->assertSame('john', $result['firstName']);
        $this->assertSame('doe', $result['lastName']);
    }

    /**
     *
     */
    public function test_inline_property_with_scalar_value()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new InlineEntity();
        $object->age = 18;
        $object->entity = 'foo';

        $result = $serializer->toArray($object);

        $this->assertSame(18, $result['age']);
        $this->assertSame('foo', $result['entity']);
    }

    /**
     *
     */
    public function test_inline_with_metadata()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new InlineEntity();
        $object->age = 18;
        $object->entity = new EntityWithValueToSkip();

        $result = $serializer->toArray($object, [NormalizationContext::META_TYPE => true]);

        $this->assertSame(18, $result['data']['age']);
        $this->assertSame('john', $result['data']['entity']['data']['firstName']);
        $this->assertSame('doe', $result['data']['entity']['data']['lastName']);
    }

    /**
     * @return PropertyNormalizer
     */
    private function getNormalizer()
    {
        $factory = $this->createMock(MetadataFactory::class);

        return new PropertyNormalizer($factory);
    }
}

//----------

class EntityWithDate
{
    public $date;
    public $basicDate;

    /**
     * @param ClassMetadataBuilder $metadata
     */
    public static function loadSerializerMetadata($metadata)
    {
        $metadata->dateTime('date')
            ->dateFormat('d/m/Y H:i:s')->timezone('+01:00')->toTimezone('+00:00')
            ->conserveNull();

        $metadata->dateTime('basicDate');
    }
}
class EntityWithCircularRef
{
    public $ref;
}
class EntityWithValueToSkip
{
    public $firstName = 'john';
    public $lastName = 'doe';

    /**
     * @param ClassMetadataBuilder $metadata
     */
    public static function loadSerializerMetadata($metadata)
    {
        $metadata->string('firstName')->conserveDefault();
        $metadata->string('lastName');
    }
}
class InlineEntity
{
    public $entity;
    public $age;

    /**
     * @param ClassMetadataBuilder $metadata
     */
    public static function loadSerializerMetadata($metadata)
    {
        $metadata->add('entity', EntityWithValueToSkip::class)->inline();
        $metadata->integer('age');
    }
}