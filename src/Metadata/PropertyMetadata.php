<?php

namespace Bdf\Serializer\Metadata;

use Bdf\Serializer\PropertyAccessor\PropertyAccessorInterface;
use Bdf\Serializer\Type\Type;

/**
 * PropertyMetadata
 * 
 * @author  Seb
 */
class PropertyMetadata
{
    /**
     * The owner class name
     *
     * @var string
     */
    public $class;

    /**
     * The property name
     *
     * @var string
     */
    public $name;

    /**
     * The property alias
     *
     * @var string
     */
    public $alias;

    /**
     * The property type infos
     *
     * @var Type
     */
    public $type;

    /**
     * The property groups
     *
     * @var array
     */
    public $groups = [];

    /**
     * The property accessor
     *
     * @var PropertyAccessorInterface
     */
    public $accessor;

    /**
     * The version when the property has been added.
     *
     * @var string
     */
    public $since;

    /**
     * The version when the property has been removed.
     *
     * @var string
     */
    public $until;

    /**
     * The read only state of the property.
     *
     * @var bool
     */
    public $readOnly = false;

    /**
     * The default value of the property.
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * The context options for normalization.
     *
     * @var null|array
     */
    public $normalizationOptions;

    /**
     * The context options for denormalization.
     *
     * @var null|array
     */
    public $denormalizationOptions;

    /**
     * PropertyMetadata constructor.
     *
     * @param string $class
     * @param string $name
     */
    public function __construct(string $class, string $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    /**
     * Check whether the property should be skipped
     *
     * @param array $groups
     *
     * @return boolean  True if the property has one of the groups
     */
    public function hasGroups(array $groups)
    {
        foreach ($groups as $group) {
            if (isset($this->groups[$group])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the property class name
     *
     * @return string
     */
    public function className()
    {
        return $this->class;
    }

    /**
     * Get the property name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the property type infos
     *
     * @param Type $type
     */
    public function setType(Type $type)
    {
        $this->type = $type;
    }

    /**
     * Get the property type
     *
     * @return Type
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Set the property alias
     *
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get the property alias
     *
     * @return string
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * Set the property groups
     *
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->groups[$group] = $group;
        }
    }

    /**
     * Get the property groups
     *
     * @return array
     */
    public function groups()
    {
        return $this->groups;
    }

    /**
     * Set the property accessor
     *
     * @param PropertyAccessorInterface $accessor
     */
    public function setAccessor(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * Get the property accessor
     *
     * @return PropertyAccessorInterface
     */
    public function accessor()
    {
        return $this->accessor;
    }

    /**
     * Set the version when the property has been added
     *
     * @param string $version
     */
    public function setSince($version)
    {
        $this->since = $version;
    }

    /**
     * Get the version when the property has been added
     *
     * @return string
     */
    public function since()
    {
        return $this->since;
    }

    /**
     * Set the version when the property has been removed
     *
     * @param string $version
     */
    public function setUntil($version)
    {
        $this->until = $version;
    }

    /**
     * Get the version when the property has been removed
     *
     * @return string
     */
    public function until()
    {
        return $this->until;
    }

    /**
     * Test if the property match to the version
     *
     * @param string $version
     *
     * @return boolean
     */
    public function matchVersion($version)
    {
        if (null !== $this->since && version_compare($version, $this->since, '<')) {
            return false;
        }

        if (null !== $this->until && version_compare($version, $this->until, '>')) {
            return false;
        }

        return true;
    }

    /**
     * Set the value of the read only flag.
     *
     * @param bool $flag
     */
    public function setReadOnly($flag)
    {
        $this->readOnly = $flag;
    }

    /**
     * Get the value of the read only flag.
     *
     * @return bool
     */
    public function readOnly()
    {
        return $this->readOnly;
    }

    /**
     * Set the value to skip.
     *
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get the value to skip.
     *
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the options for normalization context
     *
     * @param null|array $options
     */
    public function setNormalizationOptions($options)
    {
        $this->normalizationOptions = $options;
    }

    /**
     * Get property options for normalization context.
     *
     * @return null|array
     */
    public function normalizationOptions()
    {
        return $this->normalizationOptions;
    }

    /**
     * Set the options for denormalization context
     *
     * @param null|array $options
     */
    public function setDenormalizationOptions($options)
    {
        $this->denormalizationOptions = $options;
    }

    /**
     * Get property options for denormalization context.
     *
     * @return null|array
     */
    public function denormalizationOptions()
    {
        return $this->denormalizationOptions;
    }
}
