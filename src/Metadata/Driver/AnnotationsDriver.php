<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Type\Type;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionProperty;

/**
 * AnnotationsDriver
 * 
 * based on doctrine annotations
 * 
 * @author  Seb
 */
class AnnotationsDriver implements DriverInterface
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * AnnotationsDriver constructor.
     */
    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->contextFactory = new ContextFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        if ($class->isInterface() || $class->isAbstract()) {
            return null;
        }

        $annotations = [];
        $reflection = $class;

        // Get all properties annotations from the hierarchy
        do {
            foreach ($this->getClassProperties($reflection) as $property) {
                // PHP serialize behavior: we skip the static properties.
                if ($property->isStatic()) {
                    continue;
                }

                $annotation = $this->getPropertyAnnotations($property);

                if (isset($annotation['SerializeIgnore'])) {
                    continue;
                }

                if (isset($annotations[$property->name])) {
                    $annotations[$property->name] = array_merge($annotation, $annotations[$property->name]);
                } else {
                    $annotations[$property->name] = $annotation;
                }
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection);

        // Parse annotations
        $builder = new ClassMetadataBuilder($class);

        if ($class->hasMethod('__wakeup')) {
            $builder->postDenormalization('__wakeup');
        }

        foreach ($annotations as $name => $annotation) {
            $property = $builder->add($name, isset($annotation['type']) ? $annotation['type'] : Type::MIXED);

            if (isset($annotation['since'])) {
                $property->since($annotation['since']);
            }

            if (isset($annotation['until'])) {
                $property->until($annotation['until']);
            }
        }

        return $builder->build();
    }

    /**
     * Gets the class properties
     *
     * @param ReflectionClass $reflection
     *
     * @return ReflectionProperty[]
     */
    private function getClassProperties(ReflectionClass $reflection): array
    {
        if (!$reflection->hasMethod('__sleep')) {
            // The class has no magic method __sleep, we return all the properties.
            return $reflection->getProperties();
        }

        $properties = [];
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getMethod('__sleep')->invoke($instance) as $name) {
            $properties[] = $reflection->getProperty($name);
        }

        return $properties;
    }

    /**
     * Get annotations from the property
     *
     * @param ReflectionProperty $property
     *
     * @return array
     */
    private function getPropertyAnnotations(ReflectionProperty $property): array
    {
        $annotations = [];

        if (PHP_VERSION_ID >= 70400 && $property->hasType()) {
            $annotations['type'] = $this->findType($property->getType()->getName(), $property);
        }

        try {
            $docBlock = $this->docBlockFactory->create($property, $this->contextFactory->createFromReflector($property));
        } catch (\InvalidArgumentException $e) {
            return $annotations;
        }

        // Tags mapping
        foreach ($docBlock->getTags() as $tag) {
            list($option, $value) = $this->createSerializationTag($tag, $property);

            if ($option !== null && !isset($annotations[$option])) {
                $annotations[$option] = $value;
            }
        }

        return $annotations;
    }

    /**
     * Create the serialization info
     *
     * @param Tag $tag
     * @param ReflectionProperty $property
     *
     * @return array
     */
    private function createSerializationTag($tag, $property): array
    {
        switch ($tag->getName()) {
            case 'var':
                /** @var DocBlock\Tags\Var_ $tag */
                return ['type', $this->findType((string)$tag->getType(), $property)];

            case 'since':
                /** @var DocBlock\Tags\Since $tag */
                return ['since', (string)$tag->getVersion()];

            case 'until':
                /** @var DocBlock\Tags\Generic $tag */
                return ['until', (string)$tag->getDescription()];

            case 'SerializeIgnore':
                return ['SerializeIgnore', true];
        }

        return [null, null];
    }

    /**
     * Filter the var tag
     *
     * @param string $var
     * @param ReflectionProperty $property
     *
     * @return string
     */
    private function findType($var, $property): ?string
    {
        // All known alias from phpdoc that should be mapped to a serializer type
        $alias = [
            'bool' => Type::BOOLEAN,
            'false' => Type::BOOLEAN,
            'true' => Type::BOOLEAN,
            'int' => Type::INTEGER,
            'void' => Type::TNULL,
            'scalar' => Type::STRING,
            'iterable' => Type::TARRAY,
            'object' => \stdClass::class,
            'callback' => 'callable',
            'self' => $property->class,
            '$this' => $property->class,
            'static' => $property->class,
        ];

        if (strpos($var, '|') === false) {
            $var = ltrim($var, '\\');

            return isset($alias[$var]) ? $alias[$var] : $var;
        }

        foreach (explode('|', $var) as $candidate) {
            $candidate = ltrim($candidate, '\\');

            if (isset($alias[$candidate])) {
                $candidate = $alias[$candidate];
            }

            if ($candidate !== '' && $candidate !== Type::TNULL) {
                return $candidate;
            }
        }

        // We let here the getMetadataForClass add the default type
        return null;
    }
}
