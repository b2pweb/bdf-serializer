<?php

namespace Bdf\Serializer\Type;

/**
 * Type
 */
final class Type
{
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const DOUBLE = 'double';
    const TARRAY = 'array';
    const TNULL = 'null';
    const MIXED = 'mixed';

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isBuildin;

    /**
     * @var boolean
     */
    private $isArray;

    /**
     * @var Type
     */
    private $subType;

    /**
     * @var object
     */
    private $target;

    /**
     * Type constructor.
     *
     * @param string $name
     * @param bool $isBuildin
     * @param bool $isArray
     * @param Type|null $subType
     * @param null|object $target
     */
    public function __construct(string $name, bool $isBuildin, bool $isArray = false, Type $subType = null, $target = null)
    {
        $this->name = $name;
        $this->isArray = $isArray;
        $this->subType = $subType;
        $this->isBuildin = $isBuildin;
        $this->target = $target;
    }

    /**
     * Returns the type name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Check if the type is an array
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->isArray;
    }

    /**
     * Get the sub type (ex: Type<SubType> will return SubType)
     *
     * @return Type
     */
    public function subType(): ?Type
    {
        return $this->subType;
    }

    /**
     * Check if the current type is parametrized (i.e. Has a sub type)
     *
     * @return bool
     */
    public function isParametrized(): bool
    {
        return $this->subType !== null;
    }

    /**
     * Is a build in php type
     *
     * @return boolean
     */
    public function isBuildin(): bool
    {
        return $this->isBuildin;
    }

    /**
     * Get the target object of the type
     *
     * @return null|object
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * Set the target
     *
     * @param object $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Check whether the type is mixed
     *
     * @return boolean
     */
    public function isMixed(): bool
    {
        return $this->name === self::MIXED;
    }

    /**
     * Add a hint on type from the value
     * Allowed the type to guess what is the real type to manage.
     *
     * Note: The current type instance is never modified by this method
     *
     * @param mixed &$value in/out parameter for the value. If a metadata type "@type" is found, the real data is set
     *
     * @return Type The new type corresponding to the given value
     */
    public function hint(&$value): Type
    {
        if (isset($value['@type'])) {
            $type = TypeFactory::createType($value['@type']);
            // We change the type and keep the target.
            $type->target = $this->target;

            $value = $value['data'];

            return $type;
        }

        // The mixed type is a builtin type. We just need to manage array value.
        // We don't change the name of the mixed type because it is not fixed
        // and it will need to guess the next value in array case
        // We also clone the type to ensure that it'll not be modified later and cause side effects (http://redmine.b2pweb.com/issues/17469)
        if ($this->isMixed() === true) {
            $type = clone $this;

            if (is_array($value)) {
                $type->isArray = true;
                $type->subType = TypeFactory::mixedType();
            } else {
                $type->isArray = false;
                $type->subType = null;
            }

            return $type;
        }

        return $this;
    }

    /**
     * Convert the builtin value to the corresponding native type
     *
     * @param mixed $value
     *
     * @return array|bool|float|int|null|string Native value type
     */
    public function convert($value)
    {
        switch ($this->name) {
            case self::INTEGER:
                return (int) $value;

            case self::FLOAT:
                return (float) $value;

            case self::DOUBLE:
                return (double) $value;

            case self::STRING:
                return (string) $value;

            case self::BOOLEAN:
                return !! $value;

            case self::TNULL:
                return null;

            case self::TARRAY:
                return (array) $value;

            default:
                return $value;
        }
    }

    /**
     * Dont serialize target
     *
     * @return array
     */
    public function __sleep()
    {
        return [
            'name',
            'isArray',
            'subType',
            'isBuildin',
        ];
    }
}
