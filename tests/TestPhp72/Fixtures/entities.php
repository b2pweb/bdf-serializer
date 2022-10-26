<?php

namespace Bdf\Serializer\TestPhp72;


class Bar
{
    public $id;
    public $label;
}

class BarWithDefaultValue
{
    public $id;
    public $label = null;
}

class UserNonTyped
{
    private $id;
    private $name;

    public function __construct($id = null, $name = null)
    {
        $this->id   = $id;
        $this->name = $name;
    }
}
