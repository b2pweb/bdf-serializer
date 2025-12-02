<?php

namespace Bdf\Serializer;


use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 * @group Bdf_Serializer_Serializer
 */
class SerializeWithAnnotationTest extends TestCase
{
    /**
     *
     */
    public function setUp(): void
    {
        include_once __DIR__.'/Fixtures/annotations.php';
    }

    /**
     *
     */
    public function test_complete_with_types()
    {
        $serializer = SerializerBuilder::create()->build();

        $master = new Master();
        $master->has(new Dog('steven', 4, ['foo', 'bar']));
        $master->has(new Fish('richard'));

        $data = $serializer->toArray($master, ['include_type' => true]);
        $result = $serializer->fromArray($data, Master::class);

        $this->assertSame($master->animals['steven']->name(), $result->animals['steven']->name());
        $this->assertSame($master->animals['steven']->paws(), $result->animals['steven']->paws());
        $this->assertSame($master->animals['steven']->necklaces(), $result->animals['steven']->necklaces());
        $this->assertSame($master->animals['richard']->name(), $result->animals['richard']->name());
    }

    /**
     *
     */
    public function test_dont_serialize_static()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = $serializer->toArray(new Foo());

        $this->assertFalse(isset($data['static']));
        $this->assertTrue(isset($data['public']));
        $this->assertTrue(isset($data['protected']));
        $this->assertTrue(isset($data['private']));
    }

    /**
     *
     */
    public function test_sleep_method_call()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = $serializer->toArray(new Person());

        $this->assertFalse(isset($data['firstName']));
        $this->assertTrue(isset($data['lastName']));
    }

    /**
     *
     */
    public function test_wakeup_method_call()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = $serializer->toArray(new Person());
        $person = $serializer->fromArray($data, Person::class);

        $this->assertSame('reload', $person->firstName);
        $this->assertSame('doe', $person->lastName);
    }

    /**
     *
     */
    public function test_ignore_property()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = $serializer->toArray(new IgnoreProperty());

        $this->assertFalse(isset($data['toIgnore']));
        $this->assertTrue(isset($data['name']));
    }

    /**
     *
     */
    public function test_with_psalm_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $o = new WithPsalmAnnotation();
        $o->arrayStructure = ['foo' => 'bar', [1, 2, 3]];
        $o->withGenerics = new \ArrayObject([4, 5, 6]);

        $p = new Person();
        $p->firstName = 'alice';
        $p->lastName = 'smith';
        $o->withSingleGeneric = new \ArrayObject([$p]);

        $serialized = $serializer->toArray($o);

        $this->assertSame([
            'arrayStructure' => ['foo' => 'bar', [1, 2, 3]],
            'withGenerics' => [4, 5, 6],
            'withSingleGeneric' => [
                [
                    'lastName' => 'smith',
                ],
            ],
        ], $serialized);

        $expected = clone $o;
        $expected->withSingleGeneric[0]->firstName = 'reload';

        $this->assertEquals($o, $serializer->fromArray($serialized, WithPsalmAnnotation::class));
    }

    /**
     *
     */
    public function test_with_template_type()
    {
        $serializer = SerializerBuilder::create()->build();

        $o = new Lexer([
            new Token(42, 'foo'),
            new Token(5, '123'),
        ]);

        $serialized = $serializer->toArray($o);

        $this->assertSame([
            'tokens' => [
                ['key' => 42, 'value' => 'foo'],
                ['key' => 5, 'value' => '123'],
            ],
        ], $serialized);

        $this->assertEquals($o, $serializer->fromArray($serialized, Lexer::class));
    }
}
