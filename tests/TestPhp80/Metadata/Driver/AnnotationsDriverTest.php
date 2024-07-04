<?php

namespace Bdf\Serializer\TestPhp80\Metadata\Driver;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Metadata\Driver\AnnotationsDriver;
use Bdf\Serializer\TestPhp80\Metadata\Driver\Bdf\WithUnionType;
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

        include_once __DIR__.'/../../Fixtures/with_union_type.php';
    }

    public function test_load_annotations()
    {
        $driver = new AnnotationsDriver();

        $reflection = new ReflectionClass(WithUnionType::class);
        $metadata = $driver->getMetadataForClass($reflection);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertEquals(WithUnionType::class, $metadata->name());

        $this->assertEquals(Type::MIXED, $metadata->property('id')->type()->name());
    }
}
