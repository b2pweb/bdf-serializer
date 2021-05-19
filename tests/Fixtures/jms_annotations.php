<?php

namespace Bdf\Serializer\Metadata\Driver\JMS;

use JMS\Serializer\Annotation as Serializer;
use Test\Bdf\Serializer\Loader\Driver\JMS\Address as MyTestAddress;
use DateTime;

class AbstractUser
{
    /**
     * @Serializer\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\Type(DateTime::class)
     *
     * @var null|DateTime
     */
    protected $date;

    /**
     * @Serializer\Groups({"web"})
     * @Serializer\Since("1.0.0")
     * @Serializer\Until("2.0.0")
     * @Serializer\Type(MyTestAddress::class)
     *
     * @var MyTestAddress
     */
    protected $address;
}

class User extends AbstractUser
{
    protected $name;

    /**
     * @Serializer\Type(Customer::class)
     *
     * @var Customer
     */
    protected $customer;
}

class Customer
{
    /**
     * @Serializer\Type("int")
     */
    protected $id;

    protected $name;
}


namespace Test\Bdf\Serializer\Loader\Driver\JMS;

class Address
{
    /**
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $city;
}