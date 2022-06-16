<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Exception\UnexpectedValueException;

/**
 * NormalizerLoader
 */
class NormalizerLoader implements NormalizerLoaderInterface
{
    /**
     * Normalizers associated to a class name
     *
     * @var NormalizerInterface[]
     */
    private $cached = [];

    /**
     * Available normalizers
     *
     * @var NormalizerInterface[]
     */
    private $normalizers = [];

    /**
     * NormalizerLoader constructor.
     *
     * @param NormalizerInterface[]  $normalizers
     */
    public function __construct(array $normalizers)
    {
        foreach ($normalizers as $normalizer) {
            $this->addNormalizer($normalizer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addNormalizer(NormalizerInterface $normalizer)
    {
        if ($normalizer instanceof AutoRegisterInterface) {
            $normalizer->registerTo($this);
        } else {
            $this->normalizers[] = $normalizer;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function associate($className, NormalizerInterface $normalizer)
    {
        $this->cached[$className] = $normalizer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizer($className): NormalizerInterface
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        // Normalizer is already loaded
        if (isset($this->cached[$className])) {
            return $this->cached[$className];
        }

        // Find from resolvers
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($className)) {
                $this->associate($className, $normalizer);

                return $normalizer;
            }
        }

        throw new UnexpectedValueException('Cannot find normalizer for the class "'.$className.'"');
    }
}
