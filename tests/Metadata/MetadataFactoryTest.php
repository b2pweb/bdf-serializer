<?php

namespace Bdf\Serializer\Metadata;

use Bdf\Serializer\Exception\UnexpectedValueException;
use Bdf\Serializer\Metadata\Driver\StaticMethodDriver;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Metadata
 */
class MetadataFactoryTest extends TestCase
{
    /**
     *
     */
    public function test_getter()
    {
        $drivers = [
            $driver = new StaticMethodDriver('loadSerializerMetadata')
        ];
        $loader = new MetadataFactory($drivers);

        $this->assertSame(1, count($loader->getDrivers()));
        $this->assertSame([$driver], $loader->getDrivers());
        $this->assertSame(null, $loader->getCache());
    }

    /**
     *
     */
    public function test_get_set_cache()
    {
        $cache = $this->createMock(CacheInterface::class);
        $loader = new MetadataFactory([], $cache);

        $this->assertSame($cache, $loader->getCache());
    }

    /**
     *
     */
    public function test_basic_load()
    {
        $loader = $this->getFactory();

        $this->assertInstanceOf(ClassMetadata::class, $loader->getMetadata(Customer::class));
    }

    /**
     *
     */
    public function test_unknown_class()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->getFactory()->getMetadata('unknown');
    }

    /**
     *
     */
    public function test_could_not_find_normalizer()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->getFactory()->getMetadata(User::class);
    }

    /**
     * @return MetadataFactory
     */
    private function getFactory()
    {
        $driver = [
            new StaticMethodDriver('loadSerializerMetadata')
        ];
        return new MetadataFactory($driver);
    }
}


class Customer
{
    protected $id;
    protected $name;

    public static function loadSerializerMetadata($metadata)
    {
        $metadata->property('id')->configure([
            'type'  => 'integer',
            'group' => ['all', 'identifier'],
        ]);
        $metadata->property('name')->configure([
            'type'  => 'string',
            'group' => 'all',
        ]);
    }
}

class User
{
    protected $id;
    protected $name;
}