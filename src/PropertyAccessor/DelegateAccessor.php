<?php

namespace Bdf\Serializer\PropertyAccessor;

/**
 * DelegateAccessor
 */
class DelegateAccessor implements PropertyAccessorInterface
{
    /**
     * The reader accessor
     *
     * @var PropertyAccessorInterface
     */
    private $reader;

    /**
     * The writer accessor
     *
     * @var PropertyAccessorInterface
     */
    private $writer;

    /**
     * DelegateAccessor constructor.
     *
     * @param PropertyAccessorInterface $reader
     * @param PropertyAccessorInterface $writer
     */
    public function __construct(PropertyAccessorInterface $reader, PropertyAccessorInterface $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function write($object, $value)
    {
        $this->writer->write($object, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function read($object)
    {
        return $this->reader->read($object);
    }

    /**
     * Get the writer accessor
     *
     * @return PropertyAccessorInterface
     */
    public function getWriter(): PropertyAccessorInterface
    {
        return $this->writer;
    }

    /**
     * Get the reader accessor
     *
     * @return PropertyAccessorInterface
     */
    public function getReader(): PropertyAccessorInterface
    {
        return $this->reader;
    }
}
