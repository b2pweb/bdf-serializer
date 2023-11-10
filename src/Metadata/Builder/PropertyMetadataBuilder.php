<?php

namespace Bdf\Serializer\Metadata\Builder;

use Bdf\Serializer\Context\DenormalizationContext;
use Bdf\Serializer\Context\NormalizationContext;
use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\PropertyAccessor\MethodAccessor;
use Bdf\Serializer\PropertyAccessor\PropertyAccessorInterface;
use Bdf\Serializer\Type\TypeFactory;
use Bdf\Serializer\Util\AccessorGuesser;
use ReflectionClass;
use ReflectionException;

/**
 * PropertyMetadataBuilder
 *
 * @author  Seb
 */
class PropertyMetadataBuilder
{
    /**
     * The owner reflection class
     *
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * The property name
     *
     * @var string
     */
    private $name;

    /**
     * The property alias
     *
     * @var string|null
     */
    private $alias;

    /**
     * The property type
     *
     * @var string|null
     */
    private $type;

    /**
     * The property groups
     *
     * @var array
     */
    private $groups = [];

    /**
     * The property accessor
     *
     * @var PropertyAccessorInterface|array|null
     */
    private $customAccessor;

    /**
     * The getter accessor
     *
     * @var PropertyAccessorInterface|string|null
     */
    private $getter;

    /**
     * The setter accessor
     *
     * @var PropertyAccessorInterface|string|null
     */
    private $setter;

    /**
     * The version when the property has been added.
     *
     * @var string|null
     */
    private $since;

    /**
     * The version when the property has been removed.
     *
     * @var string|null
     */
    private $until;

    /**
     * The read only state of the property.
     *
     * @var bool
     */
    private $readOnly = false;

    /**
     * The inline state of the property.
     *
     * @var bool
     */
    private $inline = false;

    /**
     * The context options for normalization.
     *
     * @var null|array
     */
    private $normalizationOptions;

    /**
     * The context options for denormalization.
     *
     * @var null|array
     */
    private $denormalizationOptions;

    /**
     * PropertyMetadataBuilder constructor.
     *
     * @param ReflectionClass $reflection
     * @param string $name
     */
    public function __construct(ReflectionClass $reflection, string $name)
    {
        $this->reflection = $reflection;
        $this->name = $name;
    }

    /**
     * Build the property metadata
     *
     * @return PropertyMetadata
     */
    public function build(): PropertyMetadata
    {
        $property = new PropertyMetadata($this->reflection->name, $this->name);
        $property->setType(TypeFactory::createType($this->type));
        $property->setAlias($this->alias ?: $this->name);
        $property->setGroups($this->groups);
        $property->setAccessor($this->buildAccessor());
        $property->setSince($this->since);
        $property->setUntil($this->until);
        $property->setReadOnly($this->readOnly === true);
        $property->setInline($this->inline === true);
        $property->setNormalizationOptions($this->normalizationOptions);
        $property->setDenormalizationOptions($this->denormalizationOptions);

        $defaultValues = $this->reflection->getDefaultProperties();
        if (array_key_exists($this->name, $defaultValues)) {
            $property->setDefaultValue($defaultValues[$this->name]);
        }

        // Tag the property as typed property
        if (PHP_VERSION_ID >= 70400) {
            try {
                $property->isPhpTyped = $this->reflection->getProperty($this->name)->hasType();
            } catch (ReflectionException $exception) {
                // The property could be virtual.
            }
        }

        $this->clear();

        return $property;
    }

    /**
     * Build the property accessor
     *
     * @return PropertyAccessorInterface
     */
    private function buildAccessor(): PropertyAccessorInterface
    {
        if ($this->customAccessor instanceof PropertyAccessorInterface) {
            return $this->customAccessor;
        }

        if ($this->setter !== null || $this->getter !== null) {
            return AccessorGuesser::getMethodAccessor($this->reflection, $this->name, $this->getter, $this->setter, $this->readOnly === true);
        }

        return AccessorGuesser::getPropertyAccessor($this->reflection, $this->name);
    }

    /**
     * Set the property type
     *
     * @param null|string $type
     *
     * @return $this
     */
    public function type(?string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the property type as collection
     *
     * @return $this
     */
    public function collection()
    {
        $this->type .= '[]';

        return $this;
    }

    /**
     * Set the property type as collection of a given type
     *
     * @param string $subType
     *
     * @return $this
     */
    public function collectionOf($subType)
    {
        return $this->type($subType)->collection();
    }

    /**
     * Set the property type as collection wrapper of a given type
     *
     * @param string $subType
     *
     * @return $this
     */
    public function wrapperOf($subType)
    {
        $this->type .= "<$subType>";

        return $this;
    }

    /**
     * Set the property alias
     *
     * @param string|null $alias
     *
     * @return $this
     */
    public function alias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get the property alias
     *
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Set the property groups
     *
     * @param array $groups
     *
     * @return $this
     */
    public function groups(array $groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Add a property group
     *
     * @param string $group
     *
     * @return $this
     */
    public function addGroup(string $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Set the property accessor
     *
     * @param PropertyAccessorInterface $accessor
     *
     * @return $this
     */
    public function accessor(PropertyAccessorInterface $accessor)
    {
        $this->customAccessor = $accessor;

        return $this;
    }

    /**
     * Set the property accessor
     *
     * A accessor as string will be considered as a method
     *
     * @param PropertyAccessorInterface|string $getter
     *
     * @return $this
     */
    public function readWith($getter)
    {
        $this->getter = $getter;

        return $this;
    }

    /**
     * Set the property accessor
     *
     * @param PropertyAccessorInterface|string $setter
     *
     * @return $this
     */
    public function writeWith($setter)
    {
        $this->setter = $setter;

        return $this;
    }

    /**
     * Set a virtual property.
     *
     * @param string $getter
     *
     * @return $this
     */
    public function virtual(string $getter)
    {
        $this->readWith($getter);
        $this->readOnly();

        return $this;
    }

    /**
     * Set the version when the property has been added
     *
     * @param string $version
     *
     * @return $this
     */
    public function since(string $version)
    {
        $this->since = $version;

        return $this;
    }

    /**
     * Set the property read only.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function readOnly(bool $flag = true)
    {
        $this->readOnly = $flag;

        return $this;
    }

    /**
     * Set the property inline.
     * The properties of this property will be added as the same level.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function inline(bool $flag = true)
    {
        $this->inline = $flag;

        return $this;
    }

    /**
     * Set the version when the property has been removed
     *
     * @param string $version
     *
     * @return $this
     */
    public function until(string $version)
    {
        $this->until = $version;

        return $this;
    }

    /**
     * Add a normalization option
     *
     * @param string $option
     * @param mixed $value
     *
     * @return $this
     */
    public function normalization(string $option, $value)
    {
        $this->normalizationOptions[$option] = $value;

        return $this;
    }

    /**
     * Add a denormalization option
     *
     * @param string $option
     * @param mixed $value
     *
     * @return $this
     */
    public function denormalization(string $option, $value)
    {
        $this->denormalizationOptions[$option] = $value;

        return $this;
    }

    /**
     * Set the date time format
     *
     * @param string $format
     *
     * @return $this
     */
    public function dateFormat(string $format)
    {
        $this->normalization(NormalizationContext::DATETIME_FORMAT, $format);
        $this->denormalization(DenormalizationContext::DATETIME_FORMAT, $format);

        return $this;
    }

    /**
     * Set the internal date timezone
     *
     * @param string $timezone
     *
     * @return $this
     */
    public function timezone(string $timezone)
    {
        $this->denormalization(DenormalizationContext::TIMEZONE, $timezone);

        return $this;
    }

    /**
     * Set the serialized date timezone
     *
     * @param string $timezone
     *
     * @return $this
     */
    public function toTimezone(string $timezone)
    {
        $this->normalization(NormalizationContext::TIMEZONE, $timezone);
        $this->denormalization(DenormalizationContext::TIMEZONE_HINT, $timezone);

        return $this;
    }

    /**
     * The property will be normalize if its value is null
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function conserveNull(bool $flag = true)
    {
        $this->normalization(NormalizationContext::NULL, $flag);

        return $this;
    }

    /**
     * Keep the default value of the property
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function conserveDefault(bool $flag = false)
    {
        $this->normalization(NormalizationContext::REMOVE_DEFAULT_VALUE, $flag);

        return $this;
    }

    /**
     * Import options in the builder
     * Legacy method, should not be used
     *
     * @param array $values
     *
     * @return $this
     */
    public function configure(array $values)
    {
        foreach ($values as $property => $value) {
            switch ($property) {
                case 'type':
                    $this->type($value);
                    break;

                case 'group':
                case 'groups':
                    $this->groups((array)$value);
                    break;

                case 'alias':
                case 'serializedName':
                    $this->alias($value);
                    break;

                case 'since':
                    $this->since($value);
                    break;

                case 'until':
                    $this->until($value);
                    break;

                case 'readOnly':
                    $this->readOnly((bool)$value);
                    break;
            }
        }

        return $this;
    }

    /**
     * Clear all reference
     */
    private function clear(): void
    {
        $this->customAccessor = $this->setter = $this->getter = null;
    }
}
