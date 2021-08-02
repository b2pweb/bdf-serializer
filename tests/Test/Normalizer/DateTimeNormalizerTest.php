<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Serializer;
use Bdf\Serializer\Type\TypeFactory;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 */
class DateTimeNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_default_normalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new NormalizationContext($serializer);
        $normalizer = new DateTimeNormalizer();

        $date = new DateTime('2017-06-28T12:32:26+00:00');

        $this->assertEquals($date->format(DateTime::ATOM), $normalizer->normalize($date, $context));
    }

    /**
     *
     */
    public function test_default_denormalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new DenormalizationContext($serializer);
        $type = TypeFactory::createType(DateTime::class);
        $normalizer = new DateTimeNormalizer();

        $date = new DateTime('2017-06-28T12:32:26+00:00');

        $this->assertEquals($date, $normalizer->denormalize($date->format(DateTime::ATOM), $type, $context));
        $this->assertEquals('+00:00', $date->getTimezone()->getName());
        $this->assertEquals('12:32:26', $date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_immutable_normalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new NormalizationContext($serializer);
        $normalizer = new DateTimeNormalizer(DateTime::ATOM);

        $date = new DateTimeImmutable('2017-06-28T12:32:26+00:00');

        $this->assertEquals($date->format(DateTime::ATOM), $normalizer->normalize($date, $context));
    }

    /**
     *
     */
    public function test_immutable_denormalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new DenormalizationContext($serializer);
        $type = TypeFactory::createType(DateTimeImmutable::class);
        $normalizer = new DateTimeNormalizer();

        $date = new DateTimeImmutable('2017-06-28T12:32:26+00:00');

        $this->assertEquals($date, $normalizer->denormalize($date->format(DateTime::ATOM), $type, $context));
        $this->assertEquals('+00:00', $date->getTimezone()->getName());
        $this->assertEquals('12:32:26', $date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_custom_normalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new NormalizationContext($serializer, [
            NormalizationContext::DATETIME_FORMAT => DateTime::ATOM,
        ]);
        $normalizer = new DateTimeNormalizer();

        $date = new DateTime('2017-06-28T12:32:26+00:00');

        $this->assertEquals($date->format(DateTime::ATOM), $normalizer->normalize($date, $context));
    }

    /**
     *
     */
    public function test_custom_normalization_with_timezone()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new NormalizationContext($serializer, [
            NormalizationContext::DATETIME_FORMAT => DateTime::ATOM,
            NormalizationContext::TIMEZONE => '+02:00',
        ]);
        $normalizer = new DateTimeNormalizer();

        $date = new DateTime('2017-06-28T12:32:26+00:00');

        $this->assertEquals('2017-06-28T14:32:26+02:00', $normalizer->normalize($date, $context));
        $this->assertEquals('2017-06-28T12:32:26+00:00', $date->format(DateTime::ATOM));
    }

    /**
     *
     */
    public function test_custom_denormalization()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new DenormalizationContext($serializer, [
            DenormalizationContext::DATETIME_FORMAT   => 'Ymd His',
            DenormalizationContext::TIMEZONE_HINT => '+00:00',
        ]);
        $type = TypeFactory::createType(DateTime::class);
        $normalizer = new DateTimeNormalizer();

        $expected = new DateTime('2017-06-28T12:32:26+00:00');
        $date = $normalizer->denormalize($expected->format('Ymd His'), $type, $context);

        $this->assertEquals($expected, $date);
        $this->assertEquals('+00:00', $date->getTimezone()->getName());
        $this->assertEquals('12:32:26', $date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_custom_denormalization_without_timezone()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new DenormalizationContext($serializer, [
            DenormalizationContext::TIMEZONE => '+01:00',
        ]);
        $type = TypeFactory::createType(DateTime::class);
        $normalizer = new DateTimeNormalizer();

        $expected = new DateTime('2017-06-28T12:32:26+00:00');
        $date = $normalizer->denormalize('2017-06-28T12:32:26+00:00', $type, $context);

        $this->assertEquals($expected, $date);
        $this->assertEquals('+01:00', $date->getTimezone()->getName());
        $this->assertEquals('13:32:26', $date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_custom_immutalbe_denormalization_without_timezone()
    {
        $serializer = $this->createMock(Serializer::class);
        $context = new DenormalizationContext($serializer, [
            DenormalizationContext::TIMEZONE => '+01:00',
        ]);
        $type = TypeFactory::createType(DateTimeImmutable::class);
        $normalizer = new DateTimeNormalizer();

        $expected = new DateTimeImmutable('2017-06-28T12:32:26+00:00');
        $date = $normalizer->denormalize('2017-06-28T12:32:26+00:00', $type, $context);

        $this->assertEquals($expected, $date);
        $this->assertEquals('+01:00', $date->getTimezone()->getName());
        $this->assertEquals('13:32:26', $date->format('H:i:s'));
    }

    /**
     *
     */
    public function test_supports()
    {
        $normalizer = new DateTimeNormalizer();

        $this->assertTrue($normalizer->supports(DateTime::class));
        $this->assertTrue($normalizer->supports(DateTimeImmutable::class));
        $this->assertFalse($normalizer->supports('unknown'));
    }
}