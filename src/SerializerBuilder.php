<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
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

/**
 * Builder for serializer.
 *
 * @author seb
 *
 * @psalm-consistent-constructor
 */
class SerializerBuilder
{
    /**
     * The cache for normalizer
     *
     * @var CacheInterface|null
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
     * Default options to use when denormalizing (i.e. convert serialized data to PHP data).
     *
     * @var array<string, mixed>|null
     */
    private $defaultDenormalizationOptions;

    /**
     * Default options to use when normalizing (i.e. convert PHP data to serialized data).
     *
     * @var array<string, mixed>|null
     */
    private $defaultNormalizationOptions;

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
     * @param CacheInterface $cache  The cache driver.
     *
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
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
     * Configure default option to use when denormalizing (i.e. convert serialized data to PHP data).
     *
     * @param array<string, mixed>|null $defaultDenormalizationOptions
     * @return $this
     *
     * @see DenormalizationContext
     */
    public function setDefaultDenormalizationOptions(?array $defaultDenormalizationOptions)
    {
        $this->defaultDenormalizationOptions = $defaultDenormalizationOptions;
        return $this;
    }

    /**
     * Configure default option to use when normalizing (i.e. convert PHP data to serialized data).
     *
     * @param array<string, mixed>|null $defaultNormalizationOptions
     * @return $this
     *
     * @see NormalizationContext
     */
    public function setDefaultNormalizationOptions(?array $defaultNormalizationOptions)
    {
        $this->defaultNormalizationOptions = $defaultNormalizationOptions;
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

        return new Serializer(
            new NormalizerLoader($this->normalizers),
            $this->defaultDenormalizationOptions,
            $this->defaultNormalizationOptions
        );
    }
}
