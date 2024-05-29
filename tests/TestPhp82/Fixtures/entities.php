<?php

namespace Bdf\Serializer\TestPhp82;

readonly class PrivateReadonly
{
    private int $id;
    private string $firstName;
    private ?int $age;

    public function __construct(int $id, string $firstName, ?int $age = null)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->age = $age;
    }
}

readonly class PublicReadonly
{
    public int $id;
    public string $firstName;
    public ?int $age;

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

readonly class Foo
{
    public int $id;
    public string $firstName;
    public string $lastName;

    /**
     * @var Bar
     */
    public object $bar;
}

readonly class Bar
{
    public int $id;
    public ?string $label;

    public function __construct(int $id, ?string $label = null)
    {
        $this->id = $id;
        $this->label = $label;
    }
}
