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

/**
 * Used to assist with unmarshalling json responses
 */
trait Unmarshaller
{
    /** @var \ReflectionClass */
    private static \ReflectionClass $reflClass;

    /** @var string */
    private static string $COLON_COLON_FIELDS = '::FIELDS';

    /**
     * @param string|null $json
     * @return static
     */
    public static function UnmarshalJSON(?string $json): object
    {
        if (null === $json) {
            return new static();
        }
        $decoded = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(
                sprintf(
                    'json decode error: %s',
                    json_last_error_msg()
                )
            );
        }

        // initialize new instance of containing class
        $inst = new static();

        // check once if implementing class has special field handling
        $hasDefs = \defined($inst::class . self::$COLON_COLON_FIELDS);

        // construct map of marshalled names => field names
        $fieldNameMap = $hasDefs ? $inst->fieldNameMap($inst::FIELDS) : [];

        // iterate over all keys present in decoded json
        foreach ($decoded as $name => $value) {
            // determine if field is marshalled as something else
            $field = $fieldNameMap[$name] ?? $name;

            // considered "complex" if:
            //  1. has defs
            //  2. has def for this field
            //  3. def is more than rename
            if ($hasDefs &&
                isset($inst::FIELDS[$field]) &&
                (1 < \count($inst::FIELDS[$field]) || !isset($inst::FIELDS[$field][Field::JSON_NAME]))) {
                $inst->unmarshalComplex($field, $value, $inst::FIELDS[$field]);
            } elseif (!\property_exists($inst, $field)) {
                // if the property does not exist on the object, do not unmarshall it
                continue;
            } else {
                // if we get to here, just set it
                $inst->{$field} = $value;
            }
        }
        return $inst;
    }

    /**
     * @param array $defs
     * @return array
     */
    protected function fieldNameMap(array $defs): array
    {
        $nameMap = [];
        foreach ($defs as $field => $def) {
            if (isset($def[Field::JSON_NAME])) {
                $nameMap[$def[Field::JSON_NAME]] = $field;
            }
        }
        return $nameMap;
    }

    /**
     * @param array $fieldDef
     * @return bool
     */
    protected function fieldIsNullable(array $fieldDef): bool
    {
        return $fieldDef[Field::NULLABLE] ?? false;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $type
     * @param bool $nullable
     * @return bool|float|int|string|null
     */
    private function buildScalarValue(
        string $field,
        mixed $value,
        string $type,
        bool $nullable
    ): bool|float|int|string|null {
        // if the incoming value is null...
        if (null === $value) {
            return $nullable ? null : Zero::forType($type);
        } else {
            return $value;
        }
    }

    /**
     * TODO: allow for alternative construction methods
     *
     * @param string $field
     * @param array|object $value
     * @param string $class
     * @param bool $nullable
     * @return object|null
     */
    private function buildObjectValue(string $field, mixed $value, string $class, bool $nullable): ?object
    {
        // if the incoming value is null...
        if (null === $value) {
            // ...and this field is nullable, return null
            if ($nullable) {
                return null;
            } else {
                // ... and this field must be an instance of the provided class, return empty new empty instance
                return new $class([]);
            }
        } elseif ($value instanceof $class) {
            // if the incoming value is already an instance of the class, clone it and return
            return clone $value;
        } else {
            return new $class((array)$value);
        }
    }

    /**
     * Handles scalar type field hydration
     *
     * @param string $field
     * @param mixed $value
     * @param bool $nullable
     */
    private function unmarshalScalar(string $field, mixed $value, bool $nullable): void
    {
        $this->{$field} = $this->buildScalarValue(
            $field,
            $value,
            isset($this->{$field}) ? \gettype($this->{$field}) : Type::MIXED,
            $nullable
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function unmarshalObject(string $field, mixed $value, array $def): void
    {
        // if class is not defined, die here
        if (!isset($def[Field::CLASSNAME])) {
            throw new \LogicException(
                sprintf(
                    'Field "%s" on type "%s" is missing FIELD_CLASS hydration entry: %s',
                    $field,
                    static::class,
                    var_export($def, true)
                )
            );
        }

        $this->{$field} = $this->buildObjectValue(
            $field,
            $value,
            $def[Field::CLASSNAME],
            self::fieldIsNullable($def)
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function unmarshalArray(string $field, mixed $value, array $def): void
    {
        // attempt to extract the two possible keys
        $arrType  = $def[Field::ARRAY_TYPE] ?? null;
        $arrClass = $def[Field::CLASSNAME] ?? null;

        // type is required
        if (null === $arrType) {
            throw new \DomainException(
                sprintf(
                    'Field "%s" on type "%s" definition is missing FIELD_ARRAY_TYPE value: %s',
                    $field,
                    static::class,
                    var_export($def, true)
                )
            );
        }

        // is the incoming value null?
        if (null === $value) {
            // if this value can be null'd, allow it.
            if (static::fieldIsNullable($def)) {
                $this->{$field} = null;
            }
            return;
        }

        // by the time we get here, $value must be an array
        if (!\is_array($value)) {
            throw new \RuntimeException(
                sprintf(
                    'Field "%s" on type "%s" is an array but provided value is "%s"',
                    $field,
                    static::class,
                    \gettype($value)
                )
            );
        }

        // currently, the only supported array types are scalar or objects.  everything else will require
        // a custom callback for hydration purposes.

        if (Type::OBJECT === $arrType) {
            if (null === $arrClass) {
                throw new \DomainException(
                    sprintf(
                        'Field "%s" on type "%s" definition is missing FIELD_CLASS value: %s',
                        $field,
                        static::class,
                        var_export($def, true)
                    )
                );
            }

            foreach ($value as $k => $v) {
                // todo: causes double-checking for null if value isn't null, not great...
                if (null === $v) {
                    $this->{$field}[$k] = null;
                } else {
                    $this->{$field}[$k] = $this->buildObjectValue(
                        $field,
                        $v,
                        $arrClass,
                        false
                    );
                }
            }
        } else {
            // in all other cases, just set as-is
            foreach ($value as $k => $v) {
                if (null === $v) {
                    $this->{$field}[$k] = null;
                } else {
                    $this->{$field}[$k] = $this->buildScalarValue($field, $v, $arrType, false);
                }
            }
        }
    }

    /**
     * Handles complex type field hydration
     *
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function unmarshalComplex(string $field, mixed $value, array $def): void
    {
        // if an unmarshal callback is defined, defer to that and move on.
        if (isset($def[Field::UNMARSHAL_CALLBACK])) {
            $cb = $def[Field::UNMARSHAL_CALLBACK];
            // allow for using a "setter" method
            if (\is_string($cb) && \method_exists($this, $cb)) {
                $this->{$cb}($value);
                return;
            }
            // handle all other callable input
            if (false === \call_user_func($cb, $this, $field, $value)) {
                throw new \RuntimeException(
                    sprintf(
                        'Error calling unmarshal callback "%s" for field "%s" on class "%s"',
                        var_export($cb, true),
                        $field,
                        static::class
                    )
                );
            }
            return;
        }

        // try to determine field type by first looking up the field in the definition map, then by inspecting
        // the field's default value.
        //
        // objects _must_ have an entry in the map, as they are either un-initialized at class instantiation time or
        // set to "NULL", at which point we cannot automatically determine the value type.

        if (isset($def[Field::TYPE])) {
            // if the field has a FIELD_TYPE value in the definition map
            $fieldType = $def[Field::TYPE];
        } elseif (isset($this->{$field})) {
            // if the field is set and non-null
            // todo: should this be made more clever?  perhaps reflect our way to success?
            $fieldType = \gettype($this->{$field});
        } else {
            throw new \LogicException(
                sprintf(
                    'Field "%s" on type "%s" is missing a FIELD_TYPE hydration entry: %s',
                    $field,
                    static::class,
                    var_export($def, true)
                )
            );
        }

        if (Type::OBJECT === $fieldType) {
            $this->unmarshalObject($field, $value, $def);
        } elseif (Type::ARRAY === $fieldType) {
            $this->unmarshalArray($field, $value, $def);
        } else {
            // at this point, assume scalar
            // todo: handle non-scalar types here
            $this->unmarshalScalar($field, $value, self::fieldIsNullable($def));
        }
    }
}
