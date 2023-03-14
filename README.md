## Serializer

The Bdf Serializer can normalize, hydrate / extract and encode data or object.
It use doctrine/instantiator for instancing class and phpdocumentor for reading annotations.

[![build](https://github.com/b2pweb/bdf-serializer/actions/workflows/php.yml/badge.svg)](https://github.com/b2pweb/bdf-serializer/actions/workflows/php.yml)
[![codecov](https://codecov.io/github/b2pweb/bdf-serializer/branch/master/graph/badge.svg?token=VOFSPEWYKX)](https://codecov.io/github/b2pweb/bdf-serializer)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-serializer.svg)](https://packagist.org/packages/b2pweb/bdf-serializer)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-serializer.svg)](https://packagist.org/packages/b2pweb/bdf-serializer)
[![Type Coverage](https://shepherd.dev/github/b2pweb/bdf-serializer/coverage.svg)](https://shepherd.dev/github/b2pweb/bdf-serializer)

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

NOTE: If type has not been detected in the phpdoc we try to add the typed property value added in PHP 7.4


#### JMS/serializer driver

The driver `Bdf\Serializer\Metadata\Driver\JMSAnnotationDriver` allows you to use JMS drivers. 
The JMS metadata will be used to create Bdf metadata. Only few options of the serializer is used:
* `serializedName`
* `readOnly`
* `inline`
* `sinceVersion`
* `untilVersion`
* `getter`
* `setter`
* `groups`
* `type`

NOTE: The driver works with jms/serializer > v3.0 and php > v7.2

```PHP
<?php

use Bdf\Serializer\Metadata\Driver\JMSAnnotationDriver;
use JMS\Serializer\Metadata\Driver\AnnotationDriver as BaseJMSAnnotationDriver;

$driver = new JMSAnnotationDriver(new BaseJMSAnnotationDriver(...));
```


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

Available option for `NormalizationContext` and `DenormalizationContext`.

* `throws_on_accessor_error`: By default a value is skipped if an `Error` is thrown when writting or reading on a property. This option will throw error from accessor (debug purpose).

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
