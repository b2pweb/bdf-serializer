<?php

namespace Bdf\Serializer\Metadata\Builder;

use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\Util\AccessorGuesser;
use ReflectionClass;

/**
 * ClassMetadata
 *
 * @author  Seb
 */
class ClassMetadataBuilder
{
    /**
     * The class reflection
     *
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * The properties normalizer
     *
     * @var PropertyMetadataBuilder[]
     */
    private $properties = [];

    /**
     * Does the properties use setter
     *
     * @var bool
     */
    private $useSetters = false;

    /**
     * Does the properties use getter
     *
     * @var bool
     */
    private $useGetters = false;

    /**
     * The method to call on denormalization
     *
     * @var string|null
     */
    private $postDenormalization;

    /**
     * ClassMetadataBuilder constructor.
     *
     * @param ReflectionClass $reflection
     */
    public function __construct(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Build the class normalizer
     *
     * @return ClassMetadata
     */
    public function build(): ClassMetadata
    {
        $metadata = new ClassMetadata($this->reflection->name);

        $alias = [];

        foreach ($this->properties as $name => $property) {
            $alias[$property->getAlias()] = $name;

            if ($this->useGetters && $method = AccessorGuesser::guessGetter($metadata->name, $name)) {
                $property->readWith($method);
            }

            if ($this->useSetters && $method = AccessorGuesser::guessSetter($metadata->name, $name)) {
                $property->writeWith($method);
            }

            $metadata->addProperty($property->build());
        }

        $metadata->setPropertyAliases($alias);
        $metadata->setPostDenormalization($this->postDenormalization);

        return $metadata;
    }

    /**
     * Get the class name
     * 
     * @return string
     */
    public function name(): string
    {
        return $this->reflection->name;
    }

    /**
     * Set the normalizer class name
     *
     * @param string $class
     *
     * @return $this
     *
     * @todo Allow the metadata to choose its normalize to use ?
     */
//    public function normalizerClass($class)
//    {
//        $this->normalizerClass = $class;
//
//        return $this;
//    }

    /**
     * Set the callback for post denormalization
     *
     * @param string $method
     *
     * @return $this
     */
    public function postDenormalization(string $method)
    {
        $this->postDenormalization = $method;

        return $this;
    }

    /**
     * Enable setter on all properties
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function useSetters(bool $flag = true)
    {
        $this->useSetters = $flag;

        return $this;
    }

    /**
     * Enable getter on all properties
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function useGetters(bool $flag = true)
    {
        $this->useGetters = $flag;

        return $this;
    }

    /**
     * Get a property normalizer
     *
     * @param string $name
     *
     * @return PropertyMetadataBuilder
     */
    public function property(string $name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return $this->add($name);
    }

    /**
     * Add a property normalizer
     *
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function add(string $name, string $type = null, array $options = [])
    {
        $property = new PropertyMetadataBuilder($this->reflection, $name);
        $property->type($type);

        return $this->properties[$name] = $property->configure($options);
    }

    /**
     * Add a array property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function collection($name, array $options = [])
    {
        return $this->add($name, Type::TARRAY, $options);
    }

    /**
     * Add a string property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function string($name, array $options = [])
    {
        return $this->add($name, Type::STRING, $options);
    }

    /**
     * Add a integer property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function integer($name, array $options = [])
    {
        return $this->add($name, Type::INTEGER, $options);
    }

    /**
     * Add a boolean property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function boolean($name, array $options = [])
    {
        return $this->add($name, Type::BOOLEAN, $options);
    }

    /**
     * Add a float property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function float($name, array $options = [])
    {
        return $this->add($name, Type::FLOAT, $options);
    }

    /**
     * Add a null property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function null($name, array $options = [])
    {
        return $this->add($name, Type::TNULL, $options);
    }

    /**
     * Add a mixed property. The property type will be guessed by the value
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function mixed($name, array $options = [])
    {
        return $this->add($name, Type::MIXED, $options);
    }

    /**
     * Add a stdClass property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function object($name, array $options = [])
    {
        return $this->add($name, \stdClass::class, $options);
    }

    /**
     * Add a DateTime property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function dateTime($name, array $options = [])
    {
        return $this->add($name, \DateTime::class, $options);
    }

    /**
     * Add a DateTimeImmutable property
     *
     * @param string $name
     * @param array  $options
     *
     * @return PropertyMetadataBuilder
     */
    public function dateTimeImmutable($name, array $options = [])
    {
        return $this->add($name, \DateTimeImmutable::class, $options);
    }
}
