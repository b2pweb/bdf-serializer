<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Lexer;
use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\Driver\Bdf\Customer;
use Bdf\Serializer\Metadata\Driver\Bdf\User;
use Bdf\Serializer\Person;
use Bdf\Serializer\Token;
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
        $this->assertEquals(\ArrayObject::class, $metadata->property('withSingleGeneric')->type()->name());
        $this->assertEquals(Person::class, $metadata->property('withSingleGeneric')->type()->subType()->name());
        $this->assertEquals('string', $metadata->property('nonEmptyString')->type()->name());
        $this->assertTrue($metadata->property('nonEmptyString')->type()->isBuildin());
    }

    /**
     * @group test
     */
    public function test_load_annotations_with_template()
    {
        $driver = new AnnotationsDriver();

        $reflection = new ReflectionClass(Lexer::class);
        $metadata = $driver->getMetadataForClass($reflection);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(Lexer::class, $metadata->name());

        $this->assertEquals('array', $metadata->property('tokens')->type()->name());
        $this->assertTrue($metadata->property('tokens')->type()->isArray());
        $this->assertEquals(Token::class, $metadata->property('tokens')->type()->subType()->name());
        $this->assertFalse($metadata->property('tokens')->type()->subType()->isArray());
        $this->assertNull($metadata->property('tokens')->type()->subType()->subType());

        $reflection = new ReflectionClass(Token::class);
        $metadata = $driver->getMetadataForClass($reflection);
        $this->assertEquals(Token::class, $metadata->name());
        $this->assertEquals('mixed', $metadata->property('key')->type()->name());
        $this->assertEquals('mixed', $metadata->property('value')->type()->name());
    }
}
