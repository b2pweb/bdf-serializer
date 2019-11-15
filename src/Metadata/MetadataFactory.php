<?php

namespace Bdf\Serializer\Metadata;

use Bdf\Serializer\Metadata\Driver\DriverInterface;
use Bdf\Serializer\Exception\UnexpectedValueException;
use Psr\SimpleCache\CacheInterface;
use ReflectionException;

/**
 * MetadataFactory
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * Metadata already resolve
     *
     * @var ClassMetadata[]
     */
    private $metadata = [];

    /**
     * The cache for metadata
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * The cache id prefix
     *
     * @var string
     */
    private $cacheId;

    /**
     * Driver for loading metadata
     *
     * @var DriverInterface[]
     */
    private $drivers;

    /**
     * MetadataFactory constructor.
     *
     * @param array      $drivers
     * @param CacheInterface|null $cache
     * @param string     $cacheId
     */
    public function __construct(array $drivers, CacheInterface $cache = null, $cacheId = 'serializer-metadata-')
    {
        $this->drivers = $drivers;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($className): ClassMetadata
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        // find from memory
        if (isset($this->metadata[$className])) {
            return $this->metadata[$className];
        }

        // Load metadata from drivers
        return $this->metadata[$className] = $this->loadMetadata($className);
    }

    /**
     * Create a class metadata for this object
     *
     * @param string $className
     *
     * @return ClassMetadata
     *
     * @throws UnexpectedValueException  if the class name has no metadata
     */
    private function loadMetadata($className): ClassMetadata
    {
        if ($this->cache !== null) {
            $cacheId = $this->getCacheKey($className);

            if ($metadata = $this->cache->get($cacheId)) {
                return $metadata;
            }
        }

        try {
            $reflection = new \ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new UnexpectedValueException('Cannot find normalizer for the class "'.$className.'"');
        }

        foreach ($this->drivers as $driver) {
            if ($metadata = $driver->getMetadataForClass($reflection)) {
                if ($this->cache !== null) {
                    $this->cache->set($cacheId, $metadata);
                }
                return $metadata;
            }
        }

        throw new UnexpectedValueException('Cannot find normalizer for the class "'.$className.'"');
    }

    /**
     * Get the drivers
     *
     * @return DriverInterface[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Get the cache
     *
     * @return CacheInterface
     */
    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Create the valid cache key from class name
     *
     * @param string $className
     *
     * @return string
     */
    private function getCacheKey(string $className): string
    {
        return $this->cacheId.str_replace('\\', '.', $className);
    }
}