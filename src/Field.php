<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Copyright 2021 Daniel Carbone (daniel.p.carbone@gmail.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

class Field
{
    //-- field meta keys
    public const TYPE               = 0;
    public const CLASSNAME          = 1;
    public const OMITEMPTY          = 2;
    public const UNMARSHAL_CALLBACK = 3;
    public const MARSHAL_CALLBACK   = 4;
    public const MARSHAL_SKIP       = 5;
    public const JSON_NAME          = 6;
    public const CHILD              = 7;

    private const KEYS = [
        self::TYPE               => 'type',
        self::CLASSNAME          => 'className',
        self::OMITEMPTY          => 'omitempty',
        self::UNMARSHAL_CALLBACK => 'unmarshallCallback',
        self::MARSHAL_CALLBACK   => 'marshallCallback',
        self::MARSHAL_SKIP       => 'marshalSkip',
        self::JSON_NAME          => 'jsonName',
        self::CHILD              => 'child',
    ];

    /** @var string */
    public string $field;

    /** @var string */
    public string $type;
    /** @var string */
    public string $className;
    /** @var bool */
    public bool $omitempty = false;
    /** @var mixed */
    public $unmarshalCallback;
    /** @var mixed */
    public $marshalCallback;
    /** @var bool */
    public bool $marshalSkip = false;
    /** @var string */
    public string $marshalledName;
    /** @var \DCarbone\Go\JSON\Field|null */
    public ?Field $child = null;

    /**
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Construct Field from array definition
     *
     * @param string $field
     * @param array $def
     * @return \DCarbone\Go\JSON\Field
     */
    public static function fromArray(string $field, array $def): Field
    {
        $field = new Field($field);
        foreach ($def as $k => $v) {
            if (!isset(self::KEYS[$k])) {
                throw new \UnexpectedValueException(sprintf('Unknown key "%s" provided', $k));
            }
            if (self::CHILD === $k) {
                if (null === $v) {
                    continue;
                } elseif (\is_array($v)) {
                    $v = Field::fromArray($k, $v);
                } elseif ($v instanceof \ReflectionProperty) {
                    $v = Field::fromPropertyReflection($v, []);
                }
                $field->child = $v;
            } else {
                $field->{$k} = $v;
            }
        }
        return $field;
    }

    /**
     * @param \ReflectionProperty $r
     * @param array $def
     * @return \DCarbone\Go\JSON\Field
     */
    public static function fromPropertyReflection(\ReflectionProperty $r, array $def): Field
    {
        $field = new Field();
        $field->marshalledName
    }
}