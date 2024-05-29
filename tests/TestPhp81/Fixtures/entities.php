<?php

namespace Bdf\Serializer\TestPhp81;

class PrivateReadonly
{
    private readonly int $id;
    private readonly string $firstName;
    private readonly ?int $age;

    public function __construct(int $id, string $firstName, ?int $age = null)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->age = $age;
    }
}

class PublicReadonly
{
    public readonly int $id;
    public readonly string $firstName;
    public readonly ?int $age;

    public function __construct(int $id, string $firstName, ?int $age = null)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->age = $age;
    }
    public function setAge(?int $age)
    {
        $this->age = $age;
    }
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }
}

class Foo
{
    public readonly int $id;
    public readonly string $firstName;
    public readonly string $lastName;

    /**
     * @var Bar
     */
    public readonly object $bar;
}

class Bar
{
    public readonly int $id;
    public readonly ?string $label;

    public function __construct(int $id, ?string $label = null)
    {
        $this->id = $id;
        $this->label = $label;
    }
}
