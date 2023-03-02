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
 *
 * @implements NormalizerInterface<mixed>
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
     * @var array<string, mixed>|null
     */
    private $defaultDenormalizationOptions;

    /**
     * @var array<string, mixed>|null
     */
    private $defaultNormalizationOptions;

    /**
     * @param NormalizerLoaderInterface $loader
     * @param array<string, mixed>|null $defaultDenormalizationOptions Default options to use when denormalizing (i.e. convert serialized data to PHP data).
     * @param array<string, mixed>|null $defaultNormalizationOptions Default options to use when normalizing (i.e. convert PHP data to serialized data).
     */
    public function __construct(NormalizerLoaderInterface $loader, ?array $defaultDenormalizationOptions = null, ?array $defaultNormalizationOptions = null)
    {
        $this->loader = $loader;
        $this->defaultDenormalizationOptions = $defaultDenormalizationOptions;
        $this->defaultNormalizationOptions = $defaultNormalizationOptions;
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
        $context = $this->normalizationContext($context);

        return json_encode($this->normalize($data, $context), $context->option('json_options', 0));
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary($data, array $context = [])
    {
        return igbinary_serialize($this->normalize($data, $this->normalizationContext($context)));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($data, array $context = [])
    {
        return $this->normalize($data, $this->normalizationContext($context));
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

        if ($context->includeMetaType()) {
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
        return $this->denormalize($data, TypeFactory::createType($type), $this->denormalizationContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function fromJson(string $json, $type, array $context = [])
    {
        $context = $this->denormalizationContext($context);

        return $this->denormalize(json_decode($json, true, 512, $context->option('json_options', 0)), TypeFactory::createType($type), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function fromBinary(string $raw, $type, array $context = [])
    {
        return $this->denormalize(igbinary_unserialize($raw), TypeFactory::createType($type), $this->denormalizationContext($context));
    }

    /**
     * {@inheritdoc}
     *
     * @template T
     * @param mixed $data
     * @param Type<T> $type
     * @return T|T[]
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
                $denormalized[$key] = $this->denormalize($value, $type->subType() ?? TypeFactory::mixedType(), $context);
            }

            return $denormalized;
        }

        if ($type->isBuildin()) {
            return $type->convert($data);
        }

        /**
         * @var NormalizerInterface<T> $normalizer
         * @psalm-suppress ArgumentTypeCoercion
         */
        $normalizer = $this->loader->getNormalizer($type->name());

        return $normalizer->denormalize($data, $type, $context);
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

    /**
     * Create a new denormalization context, overriding the default context
     *
     * @param array<string, mixed> $context
     * @return DenormalizationContext
     */
    private function denormalizationContext(array $context): DenormalizationContext
    {
        if ($this->defaultDenormalizationOptions === null) {
            return new DenormalizationContext($this, $context);
        }

        $contextObj = new DenormalizationContext($this, $this->defaultDenormalizationOptions);

        return $context ? $contextObj->duplicate($context) : $contextObj;
    }

    /**
     * Create a new normalization context, overriding the default context
     *
     * @param array<string, mixed> $context
     * @return NormalizationContext
     */
    private function normalizationContext(array $context): NormalizationContext
    {
        if ($this->defaultNormalizationOptions === null) {
            return new NormalizationContext($this, $context);
        }

        $contextObj = new NormalizationContext($this, $this->defaultNormalizationOptions);

        // Use duplicate instead of merge to handle options aliases
        return $context ? $contextObj->duplicate($context) : $contextObj;
    }
}
