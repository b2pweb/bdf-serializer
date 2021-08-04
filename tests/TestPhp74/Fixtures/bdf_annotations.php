<?php

namespace Bdf\Serializer\TestPhp74\Metadata\Driver\Bdf;


class Foo
{
    private int $id;
    private string $firstName;
    private string $lastName;

    /**
     * @var Bar
     */
    private object $bar;
}

class Bar
{
    private int $id;
    private string $label;
}
