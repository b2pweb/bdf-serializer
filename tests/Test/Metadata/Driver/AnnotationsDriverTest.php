<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\Driver\Bdf\Customer;
use Bdf\Serializer\Metadata\Driver\Bdf\User;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\WithPsalmAnnotation;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Test\Bdf\Serializer\Loader\Driver\Bdf\Address as MyTestAddress;


/**
 *
 */
class AnnotationsDriverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        include_once __DIR__.'/../../Fixtures/bdf_annotations.php';
        include_once __DIR__.'/../../Fixtures/annotations.php';
    }

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

    /**
     * @group test
     */
    public function test_load_annotations_with_psalm_types()
    {
        $driver = new AnnotationsDriver();

        $reflection = new ReflectionClass(WithPsalmAnnotation::class);
        $metadata = $driver->getMetadataForClass($reflection);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(WithPsalmAnnotation::class, $metadata->name());

        $this->assertEquals('array', $metadata->property('arrayStructure')->type()->name());
        $this->assertEquals(\ArrayObject::class, $metadata->property('withGenerics')->type()->name());
    }
}
