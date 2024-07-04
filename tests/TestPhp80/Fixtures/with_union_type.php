<?php

namespace Bdf\Serializer\TestPhp80\Metadata\Driver\Bdf;

class WithUnionType
{
    private int|string $id;

    public function __construct(int|string $id)
    {
        $this->id = $id;
    }

    public function id(): int|string
    {
        return $this->id;
    }
}
