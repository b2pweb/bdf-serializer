<?php

namespace Bdf\Serializer\Metadata\Driver;

use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use Bdf\Serializer\Metadata\ClassMetadata;
use Bdf\Serializer\Type\Type;
use Bdf\Serializer\Type\TypeExpressionParser;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionProperty;

use function explode;

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
     * @var DocBlockFactoryInterface
     */
    private $docBlockFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * All known alias from phpdoc that should be mapped to a serializer type
     *
     * @var array<string, string>
     */
    public $typeMapping = [
        'bool' => Type::BOOLEAN,
        'false' => Type::BOOLEAN,
        'true' => Type::BOOLEAN,
        'int' => Type::INTEGER,
        'void' => Type::TNULL,
        'scalar' => Type::STRING,
        'iterable' => Type::TARRAY,
        'list' => Type::TARRAY,
        'object' => \stdClass::class,
        'callback' => 'callable',
        'non-empty-string' => Type::STRING,
        'non-empty-list' => Type::TARRAY,
        'non-empty-array' => Type::TARRAY,
    ];

    /**
     * AnnotationsDriver constructor.
     *
     * @param array<string, string> $typeMapping Additional type mapping
     */
    public function __construct(array $typeMapping = [])
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->contextFactory = new ContextFactory();
        $this->typeMapping += $typeMapping;
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
            $templates = $this->getClassTemplates($reflection);

            foreach ($this->getClassProperties($reflection) as $property) {
                // PHP serialize behavior: we skip the static properties.
                if ($property->isStatic()) {
                    continue;
                }

                $annotation = $this->getPropertyAnnotations($property, $templates);

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

        /** @var array<non-empty-string, array<string, mixed>> $annotations */
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
     * @param array<string, string> $templates The class templates
     *
     * @return array
     */
    private function getPropertyAnnotations(ReflectionProperty $property, array $templates): array
    {
        try {
            $tags = $this->docBlockFactory->create($property, $this->contextFactory->createFromReflector($property))->getTags();
        } catch (\InvalidArgumentException $e) {
            $tags = [];
        }

        $annotations = [];

        // Tags mapping
        foreach ($tags as $tag) {
            list($option, $value) = $this->createSerializationTag($tag, $property, $templates);

            if ($option !== null && !isset($annotations[$option])) {
                $annotations[$option] = $value;
            }
        }

        // Adding php type if no precision has been added with annotation
        if (PHP_VERSION_ID >= 70400 && ($type = $property->getType()) && $type instanceof \ReflectionNamedType && !isset($annotations['type'])) {
            $annotations['type'] = $this->findType($type->getName(), $property, []); // Templates are not applicable here
        }

        return $annotations;
    }

    /**
     * Get the class templates
     *
     * @param ReflectionClass $class
     *
     * @return array<string, string> The key is the template name, the value is the description
     */
    private function getClassTemplates(ReflectionClass $class): array
    {
        try {
            $tags = $this->docBlockFactory->create($class, $this->contextFactory->createFromReflector($class))->getTags();
        } catch (\InvalidArgumentException $e) {
            $tags = [];
        }

        $templates = [];

        foreach ($tags as $tag) {
            if (in_array($tag->getName(), ['template', 'template-covariant', 'template-contravariant', 'psalm-template', 'phpstan-template'], true)) {
                /** @var DocBlock\Tags\BaseTag $tag */
                $parts = explode(' ', trim((string) $tag), 2);
                $type = trim($parts[0]);
                $description = trim($parts[1] ?? '');

                if ($type !== '') {
                    $templates[$type] = $description;
                    $templates[ltrim($class->getNamespaceName() . '\\' . $type, '\\')] = $description;
                }
            }
        }

        return $templates;
    }

    /**
     * Create the serialization info
     *
     * @param Tag $tag
     * @param ReflectionProperty $property
     * @param array<string, string> $templates The class templates
     *
     * @return array
     */
    private function createSerializationTag($tag, $property, array $templates): array
    {
        switch ($tag->getName()) {
            case 'var':
                if ($tag instanceof DocBlock\Tags\InvalidTag) {
                    return ['type', $this->findType((string) $tag, $property, $templates)];
                }

                /** @var DocBlock\Tags\Var_ $tag */
                return ['type', $this->findType((string)$tag->getType(), $property, $templates)];

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
     * @param array<string, string> $templates The class templates
     *
     * @return string
     */
    private function findType($var, $property, array $templates): ?string
    {
        // All known alias from phpdoc that should be mapped to a serializer type
        $alias = [
                'self' => $property->class,
                '$this' => $property->class,
                'static' => $property->class,
            ]
            + $this->typeMapping
            + array_fill_keys(array_keys($templates), Type::MIXED)  // Do not resolve the actual template type, use mixed instead
        ;

        foreach (TypeExpressionParser::parseString($var) as $intersection) {
            // Only take in account the first type of the intersection
            $candidate = $intersection[0][0];
            $candidate = ltrim($candidate, '\\');

            if (isset($alias[$candidate])) {
                $candidate = $alias[$candidate];
            }

            if ($candidate === '' || $candidate === Type::TNULL) {
                continue;
            }

            // Only support types with at most one simple generic type, so if there is more, we skip it
            if (
                count($intersection[0]) !== 2 // more than one generic type
                || count($intersection[0][1]) !== 1 // the generic type is an union
                || count($intersection[0][1][0]) !== 1 // the generic type is an intersection
            ) {
                return $candidate;
            }

            $generic = ltrim($intersection[0][1][0][0][0], '\\');

            if (isset($alias[$generic])) {
                $generic = $alias[$generic];
            }

            // Ignore more complex generic types for now
            return $candidate . '<' . $generic . '>';
        }

        // We let here the getMetadataForClass add the default type
        return null;
    }
}
