<?php

namespace Bdf\Serializer\TestPhp74\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\Driver\AnnotationsDriver;
use Bdf\Serializer\TestPhp74\Metadata\Driver\Bdf\Bar;
use Bdf\Serializer\TestPhp74\Metadata\Driver\Bdf\Foo;
use Bdf\Serializer\Type\Type;
use PHPUnit\Framework\TestCase;
use ReflectionClass;


/**
 *
 */
class AnnotationsDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        include_once __DIR__.'/../../Fixtures/bdf_annotations.php';
    }

    /**
     * @group test
     */
    public function test_load_annotations()
    {
        $driver = new AnnotationsDriver();

        $reflection = new ReflectionClass(Foo::class);
        $metadata = $driver->getMetadataForClass($reflection);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(Foo::class, $metadata->name());

        $this->assertEquals(Type::INTEGER, $metadata->property('id')->type()->name());
        $this->assertEquals(Type::STRING, $metadata->property('firstName')->type()->name());
        $this->assertEquals(Type::STRING, $metadata->property('lastName')->type()->name());
        $this->assertEquals(Bar::class, $metadata->property('bar')->type()->name());
    }

}
