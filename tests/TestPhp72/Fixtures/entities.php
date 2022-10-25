<?php

namespace Bdf\Serializer\TestPhp72;


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
