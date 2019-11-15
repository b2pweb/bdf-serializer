<?php

namespace Bdf\Serializer\Context;

use Bdf\Serializer\Exception\CircularReferenceException;
use Bdf\Serializer\Metadata\PropertyMetadata;
use Bdf\Serializer\Normalizer\NormalizerInterface;

/**
 * NormalizationContext
 *
 * context used by normalization
 */
class NormalizationContext extends Context
{
    //List of denormalization options
    const EXCLUDES = 'exclude';
    const INCLUDES = 'include';
    const GROUPS = 'groups';
    const NULL = 'null';
    const META_TYPE = 'include_type';
    const VERSION = 'version';
    const DATETIME_FORMAT = 'dateFormat';
    const TIMEZONE = 'dateTimezone';
    const CIRCULAR_REFERENCE_LIMIT = 'circular_reference_limit';
    const REMOVE_DEFAULT_VALUE = 'remove_default_value';

    /**
     * The default options of this context
     *
     * @var array
     */
    protected $defaultOptions = [
        /**
         * Properties to exclude from normalization
         *
         * @var array
         */
        self::EXCLUDES => null,
        /**
         * Properties to include from normalization
         *
         * @var array
         */
        self::INCLUDES => null,
        /**
         * Groups of properties to include
         *
         * @var array
         */
        self::GROUPS => null,
        /**
         * Null value will be added if true
         *
         * @var boolean
         */
        self::NULL => false,
        /**
         * Include the metadata '@type' in the payload
         *
         * @var boolean
         */
        self::META_TYPE => false,
        /**
         * The version of the object.
         *
         * @var string|null
         */
        self::VERSION => null,
        /**
         * The circular reference limit
         *
         * @var integer
         */
        self::CIRCULAR_REFERENCE_LIMIT => 1,
        /**
         * Default value will be removed if true
         *
         * @var boolean
         */
        self::REMOVE_DEFAULT_VALUE => false,
    ];

    /**
     * The object reference for circular reference
     *
     * @var array
     */
    private $objectReferences = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(NormalizerInterface $normalizer, array $options = [])
    {
        parent::__construct($normalizer, $this->defaultOptions);

        $this->prepareOptions($options);
    }

    /**
     * Get the context groups
     *
     * @return null|array
     */
    public function groups(): ?array
    {
        return $this->options[self::GROUPS];
    }

    /**
     * Get the context exclude properties
     *
     * @return null|array
     */
    public function excludeProperties(): ?array
    {
        return $this->options[self::EXCLUDES];
    }

    /**
     * Get the context include properties
     *
     * @return null|array
     */
    public function includeProperties(): ?array
    {
        return $this->options[self::INCLUDES];
    }

    /**
     * Should the self::NULL value be included
     *
     * @return boolean
     */
    public function shouldAddNull(): bool
    {
        return $this->options[self::NULL];
    }

    /**
     * Should the default value of a property be removed
     *
     * @return boolean
     */
    public function removeDefaultValue(): bool
    {
        return $this->options[self::REMOVE_DEFAULT_VALUE];
    }

    /**
     * Serialize to the version
     *
     * @return string
     */
    public function version(): ?string
    {
        return $this->options[self::VERSION];
    }

    /**
     * Skip the property by its value
     *
     * @param PropertyMetadata $property
     * @param mixed $value
     *
     * @return boolean   Returns true if skipped
     */
    public function skipPropertyValue(PropertyMetadata $property, $value): bool
    {
        if ($value === null) {
            return !$this->shouldAddNull();
        }

        // This does not remove the 'null' default value.
        if ($this->removeDefaultValue()) {
            return $property->defaultValue === $value;
        }

        return false;
    }

    /**
     * Should the property be skipped
     *
     * @param PropertyMetadata $property
     *
     * @return boolean   Returns true if skipped
     *
     * @todo Add custom filters
     */
    public function skipProperty(PropertyMetadata $property): bool
    {
        $groups = $this->groups();

        // A group has been set and the property is not in that group: we skip the property
        if ($groups !== null && !$property->hasGroups($groups)) {
            return true;
        }

        $version = $this->version();

        // A version has been set and the property does not match this version
        if ($version !== null && !$property->matchVersion($version)) {
            return true;
        }

        return !$this->shouldNormalizeProperty($property->class, $property->name);
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return bool
     */
    public function shouldNormalizeProperty(string $class, string $property): bool
    {
        $path = "${class}::${property}";

        $excludes = $this->excludeProperties();

        if ($excludes !== null && (isset($excludes[$property]) || isset($excludes[$path]))) {
            return false;
        }

        $includes = $this->includeProperties();

        if ($includes !== null && !isset($includes[$property]) && !isset($includes[$path])) {
            return false;
        }

        return true;
    }

    /**
     * Detects if the configured circular reference limit is reached.
     *
     * @param object $object
     *
     * @return string The object hash
     *
     * @throws CircularReferenceException If circular reference found
     */
    public function assertNoCircularReference($object): string
    {
        $objectHash = spl_object_hash($object);

        if (!isset($this->objectReferences[$objectHash])) {
            $this->objectReferences[$objectHash] = 1;

            return $objectHash;
        }

        if ($this->objectReferences[$objectHash] < $this->options[self::CIRCULAR_REFERENCE_LIMIT]) {
            $this->objectReferences[$objectHash]++;

            return $objectHash;
        }

        unset($this->objectReferences[$objectHash]);

        throw new CircularReferenceException(
            'A circular reference has been detected when serializing the object of class "'.get_class($object).'" (configured limit: '.$this->options[self::CIRCULAR_REFERENCE_LIMIT].')'
        );
    }

    /**
     * Release the object
     *
     * @param string $objectHash
     */
    public function releaseReference(string $objectHash): void
    {
        // Release the memory if depth is equal to 1
        if ($this->objectReferences[$objectHash] === 1) {
            unset($this->objectReferences[$objectHash]);

            return;
        }

        $this->objectReferences[$objectHash]--;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'group':
                case self::GROUPS:
                    $this->options[self::GROUPS] = (array)$value;
                    break;

                case 'serializeNull':
                case 'serialize_null':
                case self::NULL:
                    $this->options[self::NULL] = (bool)$value;
                    break;

                case self::EXCLUDES:
                    $this->options[self::EXCLUDES] = array_flip((array)$value);
                    break;

                case self::INCLUDES:
                    $this->options[self::INCLUDES] = array_flip((array)$value);
                    break;

                default:
                    $this->options[$name] = $value;
                    break;
            }
        }
    }
}