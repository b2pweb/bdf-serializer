<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Normalizer\NormalizerInterface;

/**
 * Context
 */
abstract class Context
{
    /**
     * The context options
     *
     * @var array
     */
    protected $options;

    /**
     * The normalizer
     *
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * Context constructor.
     *
     * @param NormalizerInterface $normalizer
     * @param array $options
     */
    public function __construct(NormalizerInterface $normalizer, array $options = [])
    {
        $this->normalizer = $normalizer;
        $this->options = $options;
    }

    /**
     * Get the root normalizer
     *
     * @return NormalizerInterface
     */
    public function root(): NormalizerInterface
    {
        return $this->normalizer;
    }

    /**
     * Get an option
     *
     * @param string $key
     * @param T $default
     *
     * @return (T is null ? mixed|null : T)
     *
     * @template T
     */
    public function option(string $key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Create new context from this one
     *
     * @param array $newOptions
     *
     * @return static
     */
    public function duplicate(array $newOptions = null): self
    {
        if ($newOptions === null) {
            return $this;
        }

        $context = clone $this;
        $context->prepareOptions($newOptions);

        return $context;
    }

    /**
     * Prepare the known options
     *
     * @param array $options
     */
    abstract protected function prepareOptions(array $options): void;
}