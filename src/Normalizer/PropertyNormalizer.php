<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Exception\UnexpectedValueException;
use Bdf\Serializer\Metadata\MetadataFactoryInterface;
use Bdf\Serializer\PropertyAccessor\Exception\AccessorException;
use Bdf\Serializer\Type\Type;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;

/**
 * PropertyNormalizer
 *
 * @author  Seb
 *
 * @implements NormalizerInterface<object>
 */
class PropertyNormalizer implements NormalizerInterface
{
    /**
     * The metadata factory
     *
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * The object instantiator
     *
     * @var InstantiatorInterface|null
     */
    private $instantiator;

    /**
     * PropertyNormalizer constructor.
     *
     * @param MetadataFactoryInterface $metadataFactory
     * @param InstantiatorInterface|null $instantiator The instanciator provider. Should returns InstantiatorInterface.
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, InstantiatorInterface $instantiator = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->instantiator = $instantiator;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress PossiblyUndefinedVariable
     */
    public function normalize($data, NormalizationContext $context)
    {
        $hash = $context->assertNoCircularReference($data);

        $normalized = [];
        $metadata = $this->metadataFactory->getMetadata($data);

        // TODO Optimize the loop with the options
        foreach ($metadata->properties as $property) {
            $propertyContext = $context->duplicate($property->normalizationOptions);

            if ($propertyContext->skipProperty($property)) {
                continue;
            }

            try {
                $value = $propertyContext->root()->normalize($property->accessor->read($data), $propertyContext);
            } catch (AccessorException $exception) {
                if ($propertyContext->throwsOnAccessorError()) {
                    throw $exception;
                }

                continue;
            }

            if ($propertyContext->skipPropertyValue($property, $value)) {
                continue;
            }

            if ($property->inline && is_array($value) && !$context->includeMetaType()) {
                $normalized += $value;
            } else {
                $normalized[$property->alias] = $value;
            }
        }

        $context->releaseReference($hash);

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        $object = $this->instantiate($type);
        $metadata = $this->metadataFactory->getMetadata($object);

        foreach ((array)$data as $name => $propertyData) {
            if (($property = $metadata->property($name)) === null) {
                continue;
            }

            $propertyContext = $context->duplicate($property->denormalizationOptions);

            if ($propertyContext->skipProperty($property)) {
                continue;
            }

            // If type is an object we should try to inject
            // the new value into the object of the owner object
            if (!$property->type->isBuildin()) {
                try {
                    $current = $property->accessor->read($object);

                    // if current is an object we put it on the queue of targets
                    if (is_object($current)) {
                        $property->type->setTarget($current);
                    }
                } catch (AccessorException $exception) {
                    // Silent mode: if value is undefined we let the next denormalize create the object
                }
            }

            try {
                $property->accessor->write(
                    $object,
                    $propertyContext->root()->denormalize($propertyData, $property->type, $propertyContext)
                );
            } catch (AccessorException $exception) {
                if ($propertyContext->throwsOnAccessorError()) {
                    throw $exception;
                }

                continue;
            } finally {
                // memory leaks
                $property->type->setTarget(null);
            }
        }

        $metadata->postDenormalization($object);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return class_exists($className);
    }

    /**
     * Instanciate an object
     *
     * @param Type<object> $type
     *
     * @return object
     *
     * @throws UnexpectedValueException  If instanciate could not instanciate type
     */
    private function instantiate($type)
    {
        if ($type->target()) {
            return $type->target();
        }

        if ($this->instantiator === null) {
            $this->instantiator = new Instantiator();
        }

        try {
            return $this->instantiator->instantiate($type->name());
        } catch (ExceptionInterface $e) {
            throw new UnexpectedValueException("Could not instantiate object '".$type->name()."'", 0, $e);
        }
    }
}
