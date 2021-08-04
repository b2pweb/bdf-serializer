<?php

namespace Bdf\Serializer\TestPhp74;

class PrivateAttribute
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private ?int $age;

    public function __construct(int $id, string $firstName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
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

class PublicAttribute
{
    public int $id;
    public string $firstName;
    private string $lastName;
    public ?int $age;

    public function __construct(int $id, string $firstName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
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
    public int $id;
    public string $firstName;
    public string $lastName;

    /**
     * @var Bar
     */
    public object $bar;
}

class Bar
{
    public int $id;
    public string $label;
}
