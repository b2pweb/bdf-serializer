<?php

namespace Bdf\Serializer\Normalizer;

use Bdf\Serializer\SerializerBuilder;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Normalizer
 * @group Bdf_Serializer_Normalizer_Closure
 */
class ClosureNormalizerTest extends TestCase
{
    /**
     *
     */
    public function test_call_closure_normalizer()
    {
        $serializer = $this->getSerializer();

        $object = function() {};

        $this->assertInstanceOf(ClosureNormalizer::class, $serializer->getLoader()->getNormalizer($object));
    }

    /**
     *
     */
    public function test_normalization()
    {
        $serializer = $this->getSerializer();

        $object = function() {};

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/SerializableClosure/', $serializer->toArray($object));
        } else {
            $this->assertRegExp('/SerializableClosure/', $serializer->toArray($object));
        }
    }

    /**
     *
     */
    public function test_denormalization()
    {
        $serializer = $this->getSerializer();

        $object = function() {};
        $data = [
            '@type' => Closure::class,
            'data'  => 'C:32:"SuperClosure\SerializableClosure":160:{a:5:{s:4:"code";s:13:"function() {}";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:47:"Bdf\Serializer\Normalizer\ClosureNormalizerTest";s:8:"isStatic";b:0;}}'
        ];

        $this->assertEquals($object, $serializer->fromArray($data, Closure::class));
    }

    /**
     *
     */
    public function test_supports()
    {
        $normalizer = new ClosureNormalizer();

        $this->assertTrue($normalizer->supports(Closure::class));
        $this->assertFalse($normalizer->supports('unknown'));
    }

    /**
     * @return \Bdf\Serializer\Serializer
     */
    private function getSerializer()
    {
        return SerializerBuilder::create()
            ->setNormalizers([new ClosureNormalizer()])
            ->build();
    }

}
