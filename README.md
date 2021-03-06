## Serializer

The Bdf Serializer can normalize, hydrate / extract and encode data or object.
It use doctrine/instantiator for instancing class and phpdocumentor for reading annotations.

[![Build Status](https://travis-ci.org/b2pweb/bdf-serializer.svg?branch=master)](https://travis-ci.org/b2pweb/bdf-serializer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2pweb/bdf-serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-serializer/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-serializer.svg)](https://packagist.org/packages/b2pweb/bdf-serializer)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-serializer.svg)](https://packagist.org/packages/b2pweb/bdf-serializer)

### Installation with Composer

```bash
composer require b2p/bdf-serializer
```

### Basic usage

```PHP
<?php

use Bdf\Serializer\SerializerBuilder;

$serializer = SerializerBuilder::create()->build();
$json = $serializer->toJson($object);
//...
```

### Declare metadata

2 drivers are available. The static method called and the annotations driver.

#### Static method driver

Declare your static method to build metadata


```PHP
<?php

use Bdf\Serializer\Metadata\Builder\ClassMetadataBuilder;
use DateTime;

class User
{
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var DateTime
     */
    private $date;
    
    /**
     * @param ClassMetadataBuilder $builder
     */
    public static function loadSerializerMetadata($builder)
    {
        $builder->integer('id');
        $builder->string('name');
        
        // You can also add group, alias, ...
        $builder->string('name')
            ->addGroup('all')
            ->alias('user_name')
            ->since('1.0.0');
            
        // DateTime options are available
        $builder->dateTime('date')
            ->dateFormat('Y/m/d H:i')
            ->timezone('+01:00')      // Use this timezone in internal
            ->toTimezone('+00:00');   // Export date with this timezone
    }
}
```

#### Annotations driver

The annotations driver use phpdocumentor/reflection-docblock. The tag `@var` will be read.
If no tag is found, the default type is `string`.

Supported tags
* `var`: This annotation specify the type of the property. This tag is mandatory for deserialization.
* `since`: Enable object versionning. The value specify starting from which version this property is available.
* `until`: Enable object versionning. The value specify until which version this property was available.
* `SerializeIgnore`: Don't serialize this property.

#### Serialization options

The `NormalizationContext` contains options for normalization.
* `exclude`: Properties to exclude from normalization .
* `include`: Properties to include from normalization.
* `groups`: Groups of properties to include.
* `null`: Null value will be added if true.
* `meta_type`: Include the metadata "@type" in the payload.
* `version`: Set the version for object that support versionning serialization.The string version should be compatible with PHP function `version_compare`.
* `circular_reference_limit`: Number of circular reference. Default 1.
* `remove_default_value`: Don't inject the value of a property if it is set to its default value.

Date time options

* `dateFormat`: Normalization option to specify the format.
* `dateTimezone`: Use the given timezone to format date.
* `timezoneHint`: Denormalization option to help to detect the timezone from input string.

Exemple:
```PHP
<?php

use \Bdf\Serializer\Context\NormalizationContext;

$object = (object)[
    "name" => "John",
    "age"  => null,
];

$builder = new \Bdf\Serializer\SerializerBuilder();
$builder->setNormalizers([new \Bdf\Serializer\Normalizer\ObjectNormalizer()]);

$serializer = $builder->build();
echo $serializer->toJson($object);
// {"name":"John"}

echo $serializer->toJson($object, [NormalizationContext::NULL => true]);
// {"name":"John","age":null}
```