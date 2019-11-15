<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Metadata\PropertyMetadata;

/**
 * DenormalizationContext
 */
class DenormalizationContext extends Context
{
    //List of denormalization options
    const DATETIME_FORMAT = 'dateFormat';
    const TIMEZONE = 'dateTimezone';
    const TIMEZONE_HINT = 'timezoneHint';

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
}