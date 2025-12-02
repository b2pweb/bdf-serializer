<?php

namespace Bdf\Serializer\Type;

use function count;
use function in_array;
use function preg_quote;
use function preg_split;

final class TypeExpressionParser
{
    public const META_TOKENS = ['&', '|', '{', '}', '<', '>', ',', '[', ']'];

    /**
     * Parse a type expression string into a structured array.
     *
     * The first array level represents union types (separated by '|').
     * The second array level represents intersection types (separated by '&').
     * Each atomic type is represented as an array where the first element is the type name,
     * and subsequent elements are arrays of generic type parameters.
     * Generic parameters themselves can be union or intersection types (so they follow the same structure).
     *
     * Example:
     * 'int' => [[['int']]]
     * 'A&B|C' => [[['A'], ['B']], [['C']]]
     * 'Map<K, V>' => [[['Map', [[['K']]], [[['V']]]]]]
     *
     * @param string $type
     * @return array
     */
    public static function parseString(string $type): array
    {
        $state = new TypeExpressionParserState(preg_split('/([' . preg_quote(implode(self::META_TOKENS)) . '])/', $type, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));

        return self::parseUnionType($state);
    }

    private static function parseUnionType(TypeExpressionParserState $state): array
    {
        $types = [];

        do {
            $types[] = self::parseIntersectionType($state);
        } while ($state->consume('|'));

        return $types;
    }

    private static function parseIntersectionType(TypeExpressionParserState $state): array
    {
        $types = [];

        do {
            $types[] = self::parseAtomicType($state);
        } while ($state->consume('&'));

        return $types;
    }

    private static function parseAtomicType(TypeExpressionParserState $state): array
    {
        $type = [
            $state->isSymbol() ? trim($state->next()) : 'mixed'
        ];

        if ($state->consume('<')) {
            array_push($type, ...self::parseGenerics($state));
            $state->consume('>');
        }

        if ($state->consume('{')) {
            // array/object shape is ignored for now
            $depth = 1;

            while ($state->hasMoreTokens() && $depth > 0) {
                if ($state->consume('{')) {
                    ++$depth;
                } elseif ($state->consume('}')) {
                    --$depth;
                } else {
                    $state->next();
                }
            }
        }

        while ($state->consume('[') && $state->consume(']')) {
            $type[0] .= '[]';
        }

        return $type;
    }

    private static function parseGenerics(TypeExpressionParserState $state): array
    {
        $types = [];

        do {
            $types[] = self::parseUnionType($state);
        } while ($state->consume(','));

        return $types;
    }
}

/**
 * @internal
 */
final class TypeExpressionParserState
{
    /**
     * @var list<string>
     */
    public $tokens;
    public $position = 0;

    /**
     * @param string[] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function isSymbol(): bool
    {
        return $this->hasMoreTokens() && !in_array($this->tokens[$this->position], TypeExpressionParser::META_TOKENS, true);
    }

    public function hasMoreTokens(): bool
    {
        return $this->position < count($this->tokens);
    }

    public function consume(string $expected): bool
    {
        if ($this->hasMoreTokens() && $this->tokens[$this->position] === $expected) {
            ++$this->position;
            return true;
        }

        return false;
    }

    public function next(): string
    {
        return $this->tokens[$this->position++];
    }
}
