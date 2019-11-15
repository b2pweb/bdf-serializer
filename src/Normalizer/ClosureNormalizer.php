<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\UnexpectedValueException;
use Bdf\Serializer\Type\Type;
use Closure;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\Exception\SuperClosureException;
use SuperClosure\Serializer;
use SuperClosure\SerializerInterface;

/**
 * ClosureNormalizer
 *
 * @author  Seb
 */
class ClosureNormalizer implements NormalizerInterface, AutoRegisterInterface
{
    /**
     * The closure serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ClosureNormalizer constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?: new Serializer(new TokenAnalyzer());
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException If the closure could not be serialize
     */
    public function normalize($object, NormalizationContext $context)
    {
        try {
            return $this->serializer->serialize($object);
        } catch (SuperClosureException $e) {
            throw new UnexpectedValueException('Could not normalize closure object', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException If the closure could not be unserialize
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        try {
            return $this->serializer->unserialize($data);
        } catch (SuperClosureException $e) {
            throw new UnexpectedValueException('Could not denormalize closure object', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return $className === Closure::class;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTo(NormalizerLoaderInterface $loader): void
    {
        $loader->associate(Closure::class, $this);
    }
}
