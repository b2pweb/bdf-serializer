<?php

namespace Bdf\Serializer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Normalizer\NormalizerInterface;
use Bdf\Serializer\Normalizer\NormalizerLoaderInterface;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\Type\TypeFactory;

/**
 * Serializer
 * 
 * @author  Seb
 */
class Serializer implements SerializerInterface, NormalizerInterface, BinarySerializerInterface
{
    /**
     * The loader of normalizers
     *
     * @var NormalizerLoaderInterface
     */
    private $loader;

    /**
     * Serializer constructor.
     *
     * @param NormalizerLoaderInterface $loader
     */
    public function __construct(NormalizerLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, array $context = [])
    {
        switch ($format) {
            case 'json':
                return $this->toJson($data, $context);

            case 'binary':
                return $this->toBinary($data, $context);

            default:
                return $this->toArray($data, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toJson($data, array $context = [])
    {
        $context = new NormalizationContext($this, $context);

        return json_encode($this->normalize($data, $context), $context->option('json_options', 0));
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary($data, array $context = [])
    {
        return igbinary_serialize($this->normalize($data, new NormalizationContext($this, $context)));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($data, array $context = [])
    {
        return $this->normalize($data, new NormalizationContext($this, $context));
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, NormalizationContext $context)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }

        if (is_array($data)) {
            $normalized = [];

            foreach ($data as $key => $value) {
                $normalized[$key] = $this->normalize($value, $context);
            }

            return $normalized;
        }

        $normalized = $this->loader->getNormalizer($data)->normalize($data, $context);

        if ($context->option(NormalizationContext::META_TYPE)) {
            return [
                '@type' => get_class($data),
                'data'  => $normalized,
            ];
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, array $context = [])
    {
        switch ($format) {
            case 'json':
                return $this->fromJson($data, $type, $context);

            case 'binary':
                return $this->fromBinary($data, $type, $context);

            default:
                return $this->fromArray($data, $type, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromArray(array $data, $type, array $context = [])
    {
        return $this->denormalize($data, TypeFactory::createType($type), new DenormalizationContext($this, $context));
    }

    /**
     * {@inheritdoc}
     */
    public function fromJson(string $json, $type, array $context = [])
    {
        $context = new DenormalizationContext($this, $context);

        return $this->denormalize(json_decode($json, true, 512, $context->option('json_options', 0)), TypeFactory::createType($type), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function fromBinary(string $data, $type, array $context = [])
    {
        return $this->denormalize(igbinary_unserialize($data), TypeFactory::createType($type), new DenormalizationContext($this, $context));
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        if (!is_scalar($data) && !is_array($data)) {
            return $data;
        }

        $type = $type->hint($data);

        if ($type->isArray()) {
            $denormalized = [];

            foreach ((array)$data as $key => $value) {
                $denormalized[$key] = $this->denormalize($value, $type->subType(), $context);
            }

            return $denormalized;
        }

        if ($type->isBuildin()) {
            return $type->convert($data);
        }

        return $this->loader->getNormalizer($type->name())->denormalize($data, $type, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return true;
    }

    /**
     * Get the normalizer loader
     *
     * @return NormalizerLoaderInterface
     */
    public function getLoader(): NormalizerLoaderInterface
    {
        return $this->loader;
    }
}
