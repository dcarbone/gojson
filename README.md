# gojson

PHP lib intended to assist in marshalling and unmarshalling JSON data from Golang-based services

## Transcoding class

The [Transcoding](src/Transcoding.php) class is a container for this package's constants and a few helper functions.

## Unmarshaller trait

The [Unmarshaller](src/Unmarshaller.php) trait is intended to be embedded within any class that you wish to unmarshall
from JSON. As a basic example:

```php
use DCarbone\Go\JSON\Transcoding;
use DCarbone\Go\JSON\Unmarshaller;

class Classname {
    use Unmarshaller;
    
    protected const FIELDS = [
        'stringField' => [
            Transcoding::FIELD_TYPE => Transcoding::STRING,
        ],
        'intField' => [
            Transcoding::FIELD_TYPE => Transcoding::INTEGER,
        ],
        'floatField' => [
            Transcoding::FIELD_TYPE => Transcoding::DOUBLE,
        ],
    ];
    
    public string $stringField;
    public int $intField;
    public float $floatField;
}

$inst = Classname::UnmarshalJSON(<<<EOT
{
    "stringField": "value",
    "intField": 1,
    "floatField": 1.1
}
EOT
);
```

## Marshaller trait

The [Marshaller](src/Marshaller.php) trait is intended to be embedded within any class that you wish to marshall to
JSON. As a basic example:

```php
use DCarbone\Go\JSON\Transcoding;

class Classname {
    use \DCarbone\Go\JSON\Marshaller;
    
    protected const FIELDS = [
        'stringField' => [
            Transcoding::FIELD_TYPE => Transcoding::STRING,
            Transcoding::FIELD_OMITEMPTY => true,
        ],
        'intField' => [
            Transcoding::FIELD_TYPE => Transcoding::INTEGER,
            Transcoding::FIELD_OMITEMPTY => true,
        ],
        'floatField' => [
            Transcoding::FIELD_TYPE => Transcoding::DOUBLE,
            Transcoding::FIELD_OMITEMPTY => true,
        ],
    ];
    
    public string $stringField = '';
    public int $intField = 1;
    public float $floatField = 0.0;
}

$inst = new Classname();
$json = $inst->MarshalJSON();
echo $json; // {"intField": 1}
```

### FIELD_X constants

|Constant|Possible Values|Description|
|---|---|---|
|`FIELD_TYPE`|`['string', 'integer', 'double', 'boolean', 'object', 'array']`|This is used during marshalling and unmarshalling to determine what to do with the provided value|
|`FIELD_CLASS`|Any valid class name|The string FQN of the class of the object of this field. This must be set when `FIELD_TYPE` === `'object'` or `FIELD_ARRAY_TYPE` === `'object'`.|
|`FIELD_ARRAY_TYPE`|`['string', 'integer', 'double', 'boolean', 'object', 'array']`|This must be set when `FIELD_TYPE` === `'array'`.|
|`FIELD_UNMARSHAL_CALLBACK`|Any representation of a callable|If defined, the callable provided to this value will be used to construct the field value, whatever it may be. The object being unmarshalled, the field name, and the field's value are passed as arguments in that order|
|`FIELD_NULLABLE`|`[true, false]`|Whether this field is "nullable"|
|`FIELD_OMITEMPTY`|`[true, false]`|Whether to omit this field from being marshalled if it contains a "zero" val at time of marshal.|
|`FIELD_MARSHAL_AS`|`['string', 'integer', 'double', 'boolean']`|If specified, the value of this field will be type-cast to the defined type at time of marshal|
|`FIELD_MARSHAL_CALLBACK`|Any representation of a callable|The object being marshalled, the field name, and the field's value are passed as arguments in that order|
|`FIELD_MARSHAL_SKIP`|`[true, false]`|If true, field will not be marshalled out to JSON.|