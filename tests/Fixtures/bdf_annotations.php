<?php

namespace Bdf\Serializer\Metadata\Driver\Bdf;

use Test\Bdf\Serializer\Loader\Driver\Bdf\Address as MyTestAddress;
use DateTime;

class AbstractUser
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var null|DateTime
     */
    protected $date;

    /**
     * @var MyTestAddress
     * @since 1.0.0
     * @until 2.0.0
     */
    protected $address;
}

class User extends AbstractUser
{
    /**
     * {@inheritdoc}
     */
    protected $id;

    protected $name;

    /**
     * @var Customer
     */
    protected $customer;
}

class Customer
{
    protected $id;

    protected $name;
}


namespace Test\Bdf\Serializer\Loader\Driver\Bdf;

class Address
{
    /**
     * @var string
     */
    protected $city;
}