<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\UnexpectedValueException;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Serializer
 */
class SerializerTest extends TestCase
{
    /**
     *
     */
    public function setUp(): void
    {
        include_once __DIR__.'/Fixtures/entities.php';
    }

    /**
     *
     */
    public function test_serialize_supports_everything()
    {
        $serializer = SerializerBuilder::create()->build();

        $this->assertTrue($serializer->supports('all'));
    }

    /**
     *
     */
    public function test_basic_serialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'name' => 'test'];
        $object = new User($data['id'], $data['name']);

        $result = $serializer->serialize($object, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_serialize_with_unknow_format()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'name' => 'test'];
        $object = new User($data['id'], $data['name']);

        $result = $serializer->serialize($object, null);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_date_serialization()
    {
        $serializer = SerializerBuilder::create()->build();

        $this->assertEquals('"2020-04-16T00:00:00+00:00"', $serializer->serialize(new \DateTime('2020-04-16', new \DateTimeZone('UTC')), 'json'));
    }

    /**
     *
     */
    public function test_date_immutable_serialization()
    {
        $serializer = SerializerBuilder::create()->build();

        $this->assertEquals('"2020-04-16T00:00:00+00:00"', $serializer->serialize(new \DateTimeImmutable('2020-04-16', new \DateTimeZone('UTC')), 'json'));
    }

    /**
     *
     */
    public function test_basic_unserialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'name' => 'test'];
        $expects = new User($data['id'], $data['name']);

        $user = $serializer->deserialize(json_encode($data), User::class, 'json');

        $this->assertEquals($expects, $user);
    }

    /**
     *
     */
    public function test_deserialize_with_unknow_format()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'name' => 'test'];
        $expects = new User($data['id'], $data['name']);

        $user = $serializer->deserialize($data, User::class, null);

        $this->assertEquals($expects, $user);
    }

    /**
     * 
     */
    public function test_serialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'testname' => 'test'];
        $object = new UserWithLoader($data['id'], $data['testname']);

        $result = $serializer->serialize($object, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     * 
     */
    public function test_to_array()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'testname' => 'test'];
        $object = new UserWithLoader($data['id'], $data['testname']);

        $result = $serializer->toArray($object);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_deserialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'testname' => 'test'];
        $object = new UserWithLoader($data['id'], $data['testname']);

        $this->assertEquals($object, $serializer->deserialize(json_encode($data), UserWithLoader::class, 'json'));
    }

    /**
     *
     */
    public function test_from_array_to_object()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1, 'testname' => 'test'];
        $object = new UserWithLoader($data['id'], $data['testname']);
        $user = new UserWithLoader();

        $serializer->fromArray($data, $user);

        $this->assertEquals($object, $user);
    }

    /**
     *
     */
    public function test_inject_part_of_object_in_object()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['testname' => 'test'];
        $object = new UserWithLoader(1, $data['testname']);
        $user = new UserWithLoader(1);

        $serializer->fromArray($data, $user);

        $this->assertEquals($object, $user);
    }

    /**
     *
     */
    public function test_serialize_deeply()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => [
                'id' => 1,
                'name' => 'customer',
            ]
        ];
        $object = new UserWithCustomer(1, 'name', new Customer(1, 'customer'));

        $result = $serializer->serialize($object, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_to_array_deeply()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => [
                'id' => 1,
                'name' => 'customer',
            ]
        ];
        $object = new UserWithCustomer(1, 'name', new Customer(1, 'customer'));

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_deserialize_deeply()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => [
                'id' => 1,
                'name' => 'customer',
            ]
        ];
        $object = new UserWithCustomer(1, 'name', new Customer(1, 'customer'));

        $this->assertEquals($object, $serializer->deserialize(json_encode($data), UserWithCustomer::class, 'json'));
    }

    /**
     *
     */
    public function test_from_array_deeply()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => [
                'id' => 1,
                'name' => 'customer',
            ]
        ];
        $object = new UserWithCustomer(1, 'name', new Customer(1, 'customer'));

        $this->assertEquals($object, $serializer->fromArray($data, UserWithCustomer::class));
    }

    /**
     *
     */
    public function test_from_array_object_deeply()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => $customer = new Customer(1, 'customer')
        ];
        $object = new UserWithCustomer(1, 'name', $customer);
        $result = $serializer->fromArray($data, UserWithCustomer::class);

        $this->assertEquals($object, $result);
        $this->assertSame($object->customer(), $result->customer());
    }

    /**
     *
     */
    public function test_from_array_into_object()
    {
        $serializer = SerializerBuilder::create()->build();

        $target = new UserWithCustomer(2, 'name', new Customer(2, 'customer'));

        $data = [
            'id' => 1,
            'customer' => [
                'id' => 1,
            ]
        ];

        $serializer->fromArray($data, $target);

        $object = new UserWithCustomer(1, 'name', new Customer(1, 'customer'));
        $this->assertEquals($object, $target);
    }

    /**
     *
     */
    public function test_load_inherit_metadata()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new CustomerChild(2, 'customer');
        $object->contact = 'seb';
        $object->email = 'seb@b2pweb.com';

        $data = [
            'id' => 2,
            'name' => 'customer',
            'contact' => 'seb',
        ];

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_load_interrupted_inheritance_metadata()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new Customer(2, 'customer');
        $object->email = 'seb@b2pweb.com';

        $data = [
            'id' => 2,
            'name' => 'customer',
        ];

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_load_child_without_method()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new CustomerChildWithoutMeta(2, 'customer');
        $object->contact = 'seb';
        $object->email = 'seb@b2pweb.com';

        $data = [
            'id' => 2,
            'name' => 'customer',
        ];

        $this->assertEquals($data, $serializer->toArray($object));
    }

    /**
     *
     */
    public function test_load_overload_meta()
    {
        $serializer = SerializerBuilder::create()->build();

        $object = new CustomerChildChangeInheritance(2, 'customer');
        $object->email = 'seb@b2pweb.com';

        $data = [
            'id' => 2,
        ];

        $this->assertEquals($data, $serializer->toArray($object, ['group' => 'all']));
    }

    /**
     *
     */
    public function test_denormalize_collection()
    {
        $serializer = SerializerBuilder::create()->build();

        $now = new \DateTime('2017-06-28T12:32:26+00:00');

        $object = new DateCollection();
        $object->date[] = $now;
        $object->date[] = $now;

        $data = [
            'date' => [
                $now->format(\DateTime::ISO8601),
                $now->format(\DateTime::ISO8601),
            ],
        ];

        $this->assertEquals($object, $serializer->fromArray($data, DateCollection::class));
    }

    /**
     *
     */
    public function test_denormalize_collection_as_array()
    {
        $serializer = SerializerBuilder::create()->build();

        $now = new \DateTime('2017-06-28T12:32:26+00:00');

        $expected[] = $now;
        $expected[] = $now;

        $data = [
            $now->format(\DateTime::ISO8601),
            $now->format(\DateTime::ISO8601),
        ];

        $this->assertEquals($expected, $serializer->fromArray($data, 'DateTime[]'));
    }

    /**
     *
     */
    public function test_deserialize_unknown_type()
    {
        $this->expectException(UnexpectedValueException::class);

        $serializer = SerializerBuilder::create()->build();
        $serializer->fromArray(['id' => 1], 'unknown');
    }

    /**
     *
     */
    public function test_deserialize_with_null_object()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'user',
            'customer' => null,
        ];

        $object = new UserWithCustomer(1, 'user');

        $result = $serializer->fromArray($data, UserWithCustomer::class);

        $this->assertEquals($object, $result);
    }

    /**
     *
     */
    public function test_deserialize_with_annotations()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'name' => 'user',
            'customer' => ['id' => 2, 'name' => 'customer'],
        ];

        $object = new UserWithAnnotations(1, 'user', new Customer(2, 'customer'));

        $result = $serializer->fromArray($data, UserWithAnnotations::class);

        $this->assertSame($object->id(), $result->id());
        $this->assertSame($object->name(), $result->name());
        $this->assertSame($object->customer()->id(), $result->customer()->id());
        $this->assertSame($object->customer()->name(), $result->customer()->name());
    }

    /**
     *
     */
    public function test_deserialize_without_annotations()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'name' => 'user',
            'customer' => ['id' => 2, 'name' => 'customer'],
        ];

        $object = new UserWithoutAnnotations(1, 'user', new Customer(2, 'customer'));

        $result = $serializer->fromArray($data, UserWithoutAnnotations::class);

        $this->assertSame($object->id(), $result->id());
        $this->assertSame($object->name(), $result->name());
        $this->assertSame($object->customer()->id(), $result->customer()['id']);
        $this->assertSame($object->customer()->name(), $result->customer()['name']);
    }

    /**
     *
     */
    public function test_cancel_collection_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            ['foo', 'bar'],
            'john',
        ];

        $normalized = $serializer->toArray($data);
        $result = $serializer->fromArray($normalized, 'array');

        $this->assertSame($data, $result);
    }

    /**
     *
     */
    public function test_cancel_collection_type_with_meta_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $normalized = [
            [
                '@type' => 'stdClass',
                'data' => ['name' => 'foo'],
            ],
            [
                '@type' => 'DateTime',
                'data' => '2017-09-09 00:00:00',
            ],
        ];

        $result = $serializer->fromArray($normalized, 'array');

        $this->assertInstanceOf(\stdClass::class, $result[0]);
        $this->assertInstanceOf(\DateTime::class, $result[1]);
    }

    /**
     *
     */
    public function test_encode_json_options()
    {
        $serializer = SerializerBuilder::create()->build();

        $result = $serializer->toJson('/tmp', ['json_options' => JSON_UNESCAPED_SLASHES]);

        $this->assertSame('"/tmp"', $result);
    }

    /**
     *
     */
    public function test_decode_json_options()
    {
        $serializer = SerializerBuilder::create()->build();

        $result = $serializer->fromJson('9999999999999999999', Type::MIXED);
        $this->assertSame(9999999999999999999, $result);

        $result = $serializer->fromJson('9999999999999999999', Type::MIXED, ['json_options' => JSON_BIGINT_AS_STRING]);
        $this->assertSame('9999999999999999999', $result);
    }

    /**
     *
     */
    public function test_decode_with_meta_type_should_not_have_side_effect_on_future_calls()
    {
        // Use wrapping object with inner attribute without a defined type
        $object = new ObjectWithUndefinedPropertyType();
        $object->attr = new User('123', 'name');

        $serializer = SerializerBuilder::create()->build();

        // Encode / decode with meta-type for loading the attribute type
        $this->assertEquals($object, $serializer->fromArray($serializer->toArray($object, [NormalizationContext::META_TYPE => true]), ObjectWithUndefinedPropertyType::class));

        // Decode without meta-type, and with a scalar value
        // The attribute should be set "as this"
        // But if there is a side-effect or the previous call, serializer will try to hydrate a User object
        $this->assertEquals(new ObjectWithUndefinedPropertyType('my string value'), $serializer->fromArray(['attr' => 'my string value'], ObjectWithUndefinedPropertyType::class));
    }
}
