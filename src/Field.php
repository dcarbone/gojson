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
    //-- field meta ids
    public const OMITEMPTY          = 0;
    public const UNMARSHAL_CALLBACK = 1;
    public const MARSHAL_CALLBACK   = 2;
    public const SKIP               = 3;
    public const JSON_NAME          = 4;
    public const ARRAY_TYPE         = 5;

    //-- field meta keys
    private const KEY_OMITEMPTY          = 'omitempty';
    private const KEY_UNMARSHAL_CALLBACK = 'unmarshalCallback';
    private const KEY_MARSHAL_CALLBACK   = 'marshalCallback';
    private const KEY_SKIP               = 'skip';
    private const KEY_JSON_NAME          = 'jsonName';
    private const KEY_ARRAY_TYPE         = 'arrayType';

    //-- field key id -> name map
    private const ID_TO_KEY = [
        self::OMITEMPTY          => self::KEY_OMITEMPTY,
        self::UNMARSHAL_CALLBACK => self::KEY_UNMARSHAL_CALLBACK,
        self::MARSHAL_CALLBACK   => self::KEY_MARSHAL_CALLBACK,
        self::SKIP               => self::KEY_SKIP,
        self::JSON_NAME          => self::KEY_JSON_NAME,
        self::ARRAY_TYPE         => self::KEY_ARRAY_TYPE,
    ];

    private const KEY_TO_ID = [
        self::KEY_OMITEMPTY          => self::OMITEMPTY,
        self::KEY_UNMARSHAL_CALLBACK => self::UNMARSHAL_CALLBACK,
        self::KEY_MARSHAL_CALLBACK   => self::MARSHAL_CALLBACK,
        self::KEY_SKIP               => self::SKIP,
        self::KEY_JSON_NAME          => self::JSON_NAME,
        self::KEY_ARRAY_TYPE         => self::ARRAY_TYPE,
    ];

    /** @var int */
    private const _GOJSON_VERSION = 0;

    /** @var string */
    public string $name;

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
    public bool $skip = false;
    /** @var string */
    public string $marshalledName;
    /** @var string */
    public string $arrayType;
    /** @var bool */
    public bool $nullable = false;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

//    public function __serialize(): array
//    {
//        $out = [];
//        foreach (self::ID_TO_KEY as $key) {
//            $out[] = $this->{$key};
//        }
//
//        $out['version'] = self::_GOJSON_VERSION;
//        $out['field']   = $this->field;
//
//        return $out;
//    }
//
//    public function __unserialize(array $data): void
//    {
//        if ([] === $data) {
//            throw new \UnexpectedValueException('Empty array provided to '.__METHOD__);
//        }
//        if (self::_GOJSON_VERSION !== $data['version']) {
//            // TODO: version conversion
//        }
//        $this->field = $data['field'];
//        foreach (self::ID_TO_KEY as $id => $key) {
//            $data[$id] = $this->{$key};
//        }
//    }

    /**
     * @param string|object$class
     * @param string|object $propertyName
     * @param array|null $def
     * @return \DCarbone\Go\JSON\Field
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function forClassProperty($class, string $propertyName, ?array $def): Field
    {
        return static::fromPropertyReflection(new \ReflectionProperty($class, $propertyName), $def);
    }

    /**
     * @param \ReflectionProperty $rp
     * @param array|null $def
     * @return \DCarbone\Go\JSON\Field
     * @throws \Exception
     */
    public static function fromPropertyReflection(\ReflectionProperty $rp, ?array $def): Field
    {
        $fieldDef = new Field($rp->getName());

        $rft = $rp->getType();

        if (null === $rft) {
            throw new \Exception(sprintf(
                'Field "%s::%s" must have a type defined',
                $rp->getDeclaringClass()->getNamespaceName(),
                $rp->getName()
            ));
        }

        $typeName = $rft->getName();
        if (in_array($typeName, Type::SCALAR)) {
            $fieldDef-> type = $typeName;
        } else {
            $fieldDef->type      = Type::OBJECT;
            $fieldDef->className = "\\${$typeName}";
        }

        $fieldDef->nullable = $rft->allowsNull();

        // if we get here and $def is empty, return early
        if (null !== $def && [] !== $def) {
            foreach (self::ID_TO_KEY as $id => $key) {
                if (isset($def[$id])) {
                    $fieldDef->{$key} = $def[$id];
                }
            }
        }

        if (Type::ARRAY === $fieldDef->type && !isset($fieldDef->arrayType)) {
            throw new \DomainException(sprintf(
                'Field "%s::%s" is of type array, but is missing ARRAY_TYPE key in its field definition',
                $rp->getDeclaringClass()->getNamespaceName(),
                $rp->getName()
            ));
        }

        return $fieldDef;
    }
}
