<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Type\Type;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Test\Bdf\Serializer\Loader\Driver\Address as MyTestAddress;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Metadata
 * @group Bdf_Serializer_Metadata_Driver
 */
class AnnotationsDriverTest extends TestCase
{
    /**
     * @group test
     */
    public function test_load_annotations()
    {
        $driver = new AnnotationsDriver();

        $reflection = new ReflectionClass(User::class);
        $metadata = $driver->getMetadataForClass($reflection);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(User::class, $metadata->name());

        $this->assertEquals(Type::INTEGER, $metadata->property('id')->type()->name());
        $this->assertEquals(DateTime::class, $metadata->property('date')->type()->name());
        $this->assertEquals(MyTestAddress::class, $metadata->property('address')->type()->name());
        $this->assertEquals('1.0.0', $metadata->property('address')->since());
        $this->assertEquals('2.0.0', $metadata->property('address')->until());
        $this->assertEquals(Type::MIXED, $metadata->property('name')->type()->name());
        $this->assertEquals(Customer::class, $metadata->property('customer')->type()->name());
    }

}


class AbstractUser
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var null|DateTime
     */
    protected $date;

    /**
     * @var MyTestAddress
     * @since 1.0.0
     * @until 2.0.0
     */
    protected $address;
}

class User extends AbstractUser
{
    /**
     * {@inheritdoc}
     */
    protected $id;

    protected $name;

    /**
     * @var Customer
     */
    protected $customer;
}

class Customer
{
    /**
     * {@inheritdoc}
     */
    protected $id;

    protected $name;
}


namespace Test\Bdf\Serializer\Loader\Driver;

class Address
{
    /**
     * @var string
     */
    protected $city;
}