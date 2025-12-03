<?php

namespace Test\Type;

use Bdf\Serializer\Type\TypeExpressionParser;
use PHPUnit\Framework\TestCase;

class TypeExpressionParserTest extends TestCase
{

    public function testParseString()
    {
        $this->assertSame([[['int']]], TypeExpressionParser::parseString('int'));
        $this->assertSame([[['\Foo']]], TypeExpressionParser::parseString('\Foo'));

        $this->assertSame([[['string']], [['int']]], TypeExpressionParser::parseString('string|int'));
        $this->assertSame([[['Foo'], ['Bar']]], TypeExpressionParser::parseString('Foo&Bar'));
        $this->assertSame([[['A'], ['B']], [['C']]], TypeExpressionParser::parseString('A&B|C'));

        $this->assertSame([[['array', [[['string']]]]]], TypeExpressionParser::parseString('array<string>'));
        $this->assertSame([[['Map', [[['K']]], [[['V']]]]]], TypeExpressionParser::parseString('Map<K, V>'));
        $this->assertSame([[['list', [[['list', [[['int']]]]]]]]], TypeExpressionParser::parseString('list<list<int>>'));

        $this->assertSame([[['list', [[['string']], [['int']]]]]], TypeExpressionParser::parseString('list<string|int>'));
        $this->assertSame([[['Foo', [[['Bar'], ['Baz']]]]]], TypeExpressionParser::parseString('Foo<Bar&Baz>'));
        $this->assertSame([[['A']], [['B', [[['C']]]]]], TypeExpressionParser::parseString('A|B<C>'));
        $this->assertSame([[['Gen', [[['A']], [['B']]], [[['C'], ['D']]]]]], TypeExpressionParser::parseString('Gen<A|B, C&D>'));

        $this->assertSame([[['A']]], TypeExpressionParser::parseString('A{a:int, b:string}'));
        $this->assertSame([[['A']]], TypeExpressionParser::parseString('A{a:array{foo: int}, b:string}'));

        $this->assertSame(
            [[
                ['Outer',
                    [[
                        ['Inner', [[['X']], [['Y']]]]
                    ]],
                    [[['Z']]]
                ]
            ]],
            TypeExpressionParser::parseString('Outer<Inner<X|Y>, Z>')
        );

        $this->assertSame([[['mixed']]], TypeExpressionParser::parseString('{foo: string}'));
        $this->assertSame([[['mixed'], ['mixed']]], TypeExpressionParser::parseString('&{>foo: string'));

        $this->assertSame([[['list', [[['\MyNs\Token', [[['T']]], [[['V']]]]]]]]], TypeExpressionParser::parseString('list<\MyNs\Token<T, V>>'));
        $this->assertSame([[['Foo[]']]], TypeExpressionParser::parseString('Foo[]'));
        $this->assertSame([[['Foo[][]', [[['int']]], [[['string']]]]]], TypeExpressionParser::parseString('Foo<int, string>[][]'));
    }
}
