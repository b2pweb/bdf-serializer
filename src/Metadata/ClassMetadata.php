<?php

namespace Bdf\Serializer\Metadata;

/**
 * ClassMetadata
 *
 * @author  Seb
 */
class ClassMetadata
{
    /**
     * The class name
     *
     * @var string
     */
    public $name;

    /**
     * The properties normalizer
     *
     * @var PropertyMetadata[]
     */
    public $properties = [];

    /**
     * The property aliases
     *
     * @var string[]
     */
    private $propertyAliases = [];

    /**
     * The post normalization method
     *
     * @var string|null
     */
    public $postDenormalization;

    /**
     * ClassMetadata constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->name = $class;
    }

    /**
     * Add a property normalizer
     *
     * @param PropertyMetadata $normalizer
     */
    public function addProperty(PropertyMetadata $normalizer)
    {
        $this->properties[$normalizer->name()] = $normalizer;
    }

    /**
     * Get a property normalizer
     *
     * @param string $name
     *
     * @return null|PropertyMetadata
     */
    public function property(string $name): ?PropertyMetadata
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        if (isset($this->propertyAliases[$name])) {
            return $this->property($this->propertyAliases[$name]);
        }

        return null;

    }

    /**
     * Get all properties normalizer
     *
     * @return PropertyMetadata[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * Get the class name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Set the property aliases
     *
     * @param array $aliases
     */
    public function setPropertyAliases(array $aliases)
    {
        $this->propertyAliases = $aliases;
    }

    /**
     * Set the post normalization method
     *
     * @param string $postDenormalization
     */
    public function setPostDenormalization(?string $postDenormalization)
    {
        $this->postDenormalization = $postDenormalization;
    }

    /**
     * Set the post normalization method
     *
     * @param object $object
     */
    public function postDenormalization($object)
    {
        if ($this->postDenormalization === null) {
            return;
        }

        $method = $this->postDenormalization;
        $object->$method();
    }
}
