<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Type\Type;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * DateTimeNormalizer
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * The default date format used for normalization
     *
     * @var string
     */
    private $defaultFormat;

    /**
     * DateTimeNormalizer constructor.
     *
     * @param string $defaultFormat   The default date format used by normalization
     */
    public function __construct(string $defaultFormat = DateTime::ATOM)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($date, NormalizationContext $context)
    {
        $timezone = $context->option(NormalizationContext::TIMEZONE);

        if ($timezone !== null) {
            // Dont change date instance if it is not immutable
            if (!$date instanceof DateTimeImmutable) {
                $date = clone $date;
            }

            $date = $date->setTimezone(new DateTimeZone($timezone));
        }

        return $date->format($context->option(NormalizationContext::DATETIME_FORMAT, $this->defaultFormat));
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, Type $type, DenormalizationContext $context)
    {
        if ($data === null) {
            return null;
        }

        $className = $type->name();
        $format = $context->option(DenormalizationContext::DATETIME_FORMAT);
        $timezoneHint = $context->option(DenormalizationContext::TIMEZONE_HINT);
        $timezone = $context->option(DenormalizationContext::TIMEZONE);

        if ($timezoneHint !== null) {
            $timezoneHint = new DateTimeZone($timezoneHint);
        }

        if ($format !== null) {
            /** @var DateTimeInterface $date */
            $date = $className::createFromFormat($format, $data, $timezoneHint);
        } else {
            /** @var DateTimeInterface $date */
            $date = new $className($data, $timezoneHint);
        }

        if ($timezone !== null) {
            $date = $date->setTimezone(new DateTimeZone($timezone));
        }

        return $date;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $className): bool
    {
        return is_subclass_of($className, DateTimeInterface::class);
    }
}
