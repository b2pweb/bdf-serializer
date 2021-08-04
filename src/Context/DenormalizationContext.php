<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\Normalizer\NormalizerInterface;

/**
 * DenormalizationContext
 */
class DenormalizationContext extends Context
{
    //List of denormalization options
    const DATETIME_FORMAT = 'dateFormat';
    const TIMEZONE = 'dateTimezone';
    const TIMEZONE_HINT = 'timezoneHint';
    const THROWS_ON_ACCESSOR_ERROR = 'throws_on_accessor_error';

    /**
     * The default options of this context
     *
     * @var array
     */
    private $defaultOptions = [
        /**
         * Throws exception if accessor has error
         *
         * @var boolean
         */
        self::THROWS_ON_ACCESSOR_ERROR => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(NormalizerInterface $normalizer, array $options = [])
    {
        parent::__construct($normalizer, $options + $this->defaultOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Should the property be skipped
     *
     * @param PropertyMetadata $property
     *
     * @return boolean   Returns true if skipped
     */
    public function skipProperty(PropertyMetadata $property): bool
    {
        return $property->readOnly;
    }

    /**
     * Should add metadata of type into the serialization
     *
     * @return bool
     */
    public function throwsOnAccessorError(): bool
    {
        return $this->options[self::THROWS_ON_ACCESSOR_ERROR];
    }
}