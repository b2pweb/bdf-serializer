<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\Driver\JMS\Customer;
use Bdf\Serializer\Metadata\Driver\JMS\User;
use Bdf\Serializer\Type\Type;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Metadata\Driver\AnnotationDriver as BaseJMSAnnotationDriver;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Test\Bdf\Serializer\Loader\Driver\JMS\Address as MyTestAddress;

/**
 *
 */
class JMSAnnotationsDriverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        include_once __DIR__.'/../../Fixtures/jms_annotations.php';
    }
    /**
     * @group test
     */
    public function test_load_annotations()
    {
        $annotation = new BaseJMSAnnotationDriver(new AnnotationReader(), new IdenticalPropertyNamingStrategy());
        $driver = new JMSAnnotationDriver($annotation);

        $metadata = $driver->getMetadataForClass(new ReflectionClass(User::class));

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
