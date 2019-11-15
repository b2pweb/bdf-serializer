<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Metadata\Driver\AnnotationsDriver;
use Bdf\Serializer\Metadata\Driver\DriverInterface;
use Bdf\Serializer\Metadata\Driver\StaticMethodDriver;
use Bdf\Serializer\Metadata\MetadataFactory;
use Bdf\Serializer\Normalizer\DateTimeNormalizer;
use Bdf\Serializer\Normalizer\NormalizerInterface;
use Bdf\Serializer\Normalizer\NormalizerLoader;
use Bdf\Serializer\Normalizer\PropertyNormalizer;
use Bdf\Serializer\Normalizer\TraversableNormalizer;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Builder for serializer.
 *
 * @author seb
 */
class SerializerBuilder
{
    /**
     * The cache for normalizer
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * The normalizers
     *
     * @var NormalizerInterface[]
     */
    private $normalizers = [];

    /**
     * The metadata drivers
     *
     * @var DriverInterface[]
     */
    private $drivers = [];

    /**
     * Create a new builder
     *
     * @return self
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Set the cache
     * 
     * @param CacheInterface|string $cache  If cache is a string, it wille be considered as filesystem cache directory.
     *
     * @return $this
     */
    public function setCache($cache)
    {
        if (is_string($cache)) {
            $cache = new Psr16Cache(new FilesystemAdapter('', 0, $cache));
        }

        $this->cache = $cache;

        return $this;
    }

    /**
     * Set the normalizers
     *
     * @param NormalizerInterface[] $normalizers
     *
     * @return $this
     */
    public function setNormalizers(array $normalizers)
    {
        $this->normalizers = $normalizers;

        return $this;
    }

    /**
     * Set the metadata drivers
     *
     * @param DriverInterface[] $drivers
     *
     * @return $this
     */
    public function setDrivers(array $drivers)
    {
        $this->drivers = $drivers;

        return $this;
    }

    /**
     * Build the serializer
     * 
     * @return Serializer
     */
    public function build()
    {
        if (!$this->normalizers) {
            if (!$this->drivers) {
                $this->drivers = [
                    new StaticMethodDriver(),
                    new AnnotationsDriver(),
                ];
            }

            $this->normalizers = [
                new DateTimeNormalizer(),
                new TraversableNormalizer(),
                new PropertyNormalizer(new MetadataFactory($this->drivers, $this->cache))
            ];
        }

        return new Serializer(new NormalizerLoader($this->normalizers));
    }
}
