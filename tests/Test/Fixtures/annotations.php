<?php

namespace Bdf\Serializer;

class Master
{
    public $animals = [];

    public function has(Animal $animal)
    {
        $this->animals[$animal->name()] = $animal;
    }
}

abstract class Animal
{
    private $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }
}

class Fish extends Animal
{
}

class Dog extends Animal
{
    private $paws;
    private $necklaces;

    public function __construct($name = null, $paws = 0, array $necklaces = [])
    {
        parent::__construct($name);

        $this->paws = $paws;
        $this->necklaces = $necklaces;
    }

    public function paws()
    {
        return $this->paws;
    }

    public function necklaces()
    {
        return $this->necklaces;
    }
}

class Foo
{
    public $public = 'public';
    protected $protected = 'protected';
    private $private = 'private';
    private static $static = 'static';
}

class Person
{
    public $firstName = 'john';
    public $lastName = 'doe';

    public function __sleep()
    {
        return ['lastName'];
    }
    public function __wakeup()
    {
        $this->firstName = 'reload';
    }
}

class IgnoreProperty
{
    /**
     * @var string
     * @SerializeIgnore
     */
    public $toIgnore = 'john';

    public $name = 'john doe';
}

class WithPsalmAnnotation
{
    /**
     * @var array{foo: string, bar: array}|null
     */
    public $arrayStructure;
    /**
     * @var \ArrayObject<int, Person>
     */
    public $withGenerics;
}
