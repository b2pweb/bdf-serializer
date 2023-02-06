<?php

namespace Bdf\Serializer;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Serializer
 */
class SerializerOptionsTest extends TestCase
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
    public function test_serialize_group()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['id' => 1];
        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->serialize($object, 'json', ['group' => 'identifier']);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     * 
     */
    public function test_serialize_exclude_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['testname' => 'name', 'customer' => ['name' => 'customer']];
        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->serialize($object, 'json', ['exclude' => 'id']);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_serialize_exclude_property_class()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['testname' => 'name', 'customer' => ['id' => 2, 'name' => 'customer']];
        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->serialize($object, 'json', ['exclude' => UserWithCustomer::class.'::id']);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     * 
     */
    public function test_serialize_include_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['testname' => 'name'];
        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->serialize($object, 'json', ['include' => 'name']);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     * 
     */
    public function test_serialize_include_property_class()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['testname' => 'name'];
        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->serialize($object, 'json', ['include' => UserWithCustomer::class.'::name']);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_serialize_with_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            '@type' => UserWithCustomer::class,
            'data' => [
                'id' => 1,
                'testname' => 'user',
                'customer' => [
                    '@type' => Customer::class,
                    'data'  => [
                        'id' => 1,
                        'name' => 'customer',
                    ]
                ]
            ],
        ];

        $object = new UserWithCustomer(1, 'user', new Customer(1, 'customer'));

        $result = $serializer->serialize($object, 'json', ['include_type' => true]);

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_deserialize_with_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            '@type' => UserWithCustomer::class,
            'data' => [
                'id' => 1,
                'testname' => 'user',
                'customer' => [
                    '@type' => Customer::class,
                    'data'  => [
                        'id' => 1,
                        'name' => 'customer',
                    ]
                ]
            ],
        ];

        $object = new UserWithCustomer(1, 'user', new Customer(1, 'customer'));

        $result = $serializer->fromArray($data, null);

        $this->assertEquals($object, $result);
    }

    /**
     *
     */
    public function test_serialize_with_null_value()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => null,
            'customer' => null,
        ];

        $object = new UserWithCustomer(1);

        $result = $serializer->toArray($object, ['null' => true]);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_serialize_with_old_version()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'customer' => [
                'id' => 2,
            ],
        ];

        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->toArray($object, ['version' => '0.0.1']);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_serialize_with_version()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'testname' => 'name',
            'customer' => [
                'id' => 2,
                'name' => 'customer',
            ],
        ];

        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->toArray($object, ['version' => '1.0.1']);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_serialize_with_current_version()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'id' => 1,
            'customer' => [
                'id' => 2,
                'name' => 'customer',
            ],
        ];

        $object = new UserWithCustomer(1, 'name', new Customer(2, 'customer'));

        $result = $serializer->toArray($object, ['version' => '3.0.1']);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_serialize_read_only()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['data' => 'seb'];

        $result = $serializer->toArray(new ReadOnlyEntity('seb'));

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function test_unserialize_read_only()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = ['data' => 'seb'];

        $result = $serializer->fromArray($data, ReadOnlyEntity::class);

        $this->assertEquals('entity', $result->data());
    }

    public function test_serialize_with_default_options()
    {
        $serializer = SerializerBuilder::create()
            ->setDefaultNormalizationOptions(['version' => '0.0.1', 'include_type' => true])
            ->build();

        $data = [
            '@type' => UserWithCustomer::class,
            'data' => [
                'id' => 1,
                'customer' => [
                    '@type' => Customer::class,
                    'data'  => [
                        'id' => 1,
                    ]
                ]
            ],
        ];

        $object = new UserWithCustomer(1, 'user', new Customer(1, 'customer'));

        $result = $serializer->serialize($object, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    public function test_serialize_with_default_options_overridden()
    {
        $serializer = SerializerBuilder::create()
            ->setDefaultNormalizationOptions(['version' => '1.0.1', 'include_type' => true])
            ->build();

        $data = [
            '@type' => UserWithCustomer::class,
            'data' => [
                'id' => 1,
                'testname' => 'user',
                'customer' => [
                    '@type' => Customer::class,
                    'data'  => [
                        'id' => 1,
                        'name' => 'customer',
                    ]
                ]
            ],
        ];

        $object = new UserWithCustomer(1, 'user', new Customer(1, 'customer'));

        $result = $serializer->serialize($object, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    public function test_unserialize_with_default_options()
    {
        $serializer = SerializerBuilder::create()
            ->setDefaultDenormalizationOptions(['dateFormat' => '!d/m/y', 'dateTimezone' => 'Europe/Paris'])
            ->build();

        $result = $serializer->fromArray(['date' => ['14/05/26', '23/12/22', '01/02/03']], DateCollection::class);

        $this->assertEquals([
            new \DateTime('2026-05-14', new \DateTimeZone('Europe/Paris')),
            new \DateTime('2022-12-23', new \DateTimeZone('Europe/Paris')),
            new \DateTime('2003-02-01', new \DateTimeZone('Europe/Paris')),
        ], $result->date);
    }

    public function test_unserialize_with_default_options_overridden()
    {
        $serializer = SerializerBuilder::create()
            ->setDefaultDenormalizationOptions(['dateFormat' => '!d/m/Y', 'dateTimezone' => 'Europe/Paris'])
            ->build();

        $result = $serializer->fromArray(['date' => ['14/05/2026', '23/12/2022', '01/02/2003']], DateCollection::class);

        $this->assertEquals([
            new \DateTime('2026-05-14', new \DateTimeZone('Europe/Paris')),
            new \DateTime('2022-12-23', new \DateTimeZone('Europe/Paris')),
            new \DateTime('2003-02-01', new \DateTimeZone('Europe/Paris')),
        ], $result->date);
    }
}
