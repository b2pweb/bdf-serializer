<?php

namespace Bdf\Serializer\PropertyAccessor;

use Closure;

/**
 * ClosureAccessor
 *
 * Use closure to access object property
 *
 * @see https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
 * @see https://github.com/Ocramius/GeneratedHydrator
 */
class ClosureAccessor implements PropertyAccessorInterface
{
    /**
     * The class name
     *
     * @var string
     */
    private $class;

    /**
     * The property name
     *
     * @var string
     */
    private $property;

    /**
     * The property reader
     *
     * @var Closure
     */
    private $reader;

    /**
     * The property writer
     *
     * @var Closure
     */
    private $writer;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $property
     */
    public function __construct(string $class, string $property)
    {
        $this->class = $class;
        $this->property = $property;
        $this->reader = $this->createReader();
        $this->writer = $this->createWriter();
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        $this->writer->__invoke($object, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        return $this->reader->__invoke($object);
    }

    /**
     * Create property accessor.
     *
     * @return Closure
     */
    private function createReader(): Closure
    {
        $property = $this->property;

        /** @var Closure */
        return Closure::bind(
            /**
             * @param object $object
             * @return mixed
             */
            function ($object) use ($property) {
                return $object->$property;
            },
            null,
            $this->class
        );
    }

    /**
     * Create property accessor.
     *
     * @return Closure
     */
    private function createWriter(): Closure
    {
        $property = $this->property;

        /** @var Closure */
        return Closure::bind(
            /**
             * @param object $object
             * @param mixed $value
             * @return void
             */
            function ($object, $value) use ($property) {
                $object->$property = $value;
            },
            null,
            $this->class
        );
    }

    /**
     * Dont serialize closures
     *
     * @return array
     */
    public function __sleep()
    {
        return ['class', 'property'];
    }

    /**
     * Rebuild closure.
     */
    public function __wakeup()
    {
        $this->reader = $this->createReader();
        $this->writer = $this->createWriter();
    }
}
