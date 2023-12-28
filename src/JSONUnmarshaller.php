<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Copyright 2021-2022 Daniel Carbone (daniel.p.carbone@gmail.com)

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

abstract class JSONUnmarshaller
{
    /**
     * Attempts to unmarshal the provided value into the provided field on the implementing class
     *
     * @param string $field
     * @param mixed $value
     */
    public static function unmarshalField(object $inst, ?array $def, string $field, $value): void
    {
        // if the implementing class has some explicitly defined overrides
        if (null !== $def && [] !== $def) {
            static::unmarshalFromTags($inst, $field, $value, $def);
            return;
        }

        // if the field isn't explicitly defined on the implementing class, just set it to whatever the incoming
        // value is
        if (!property_exists($inst, $field)) {
            $inst->{$field} = $value;
            return;
        }

        // otherwise, attempt to reflect our way to success
        $rf = new \ReflectionProperty($inst::class, $field);
        $rft = $rf->getType();
        $rftName = (string)$rft;

        // if type isn't defined on class, try to set the value as whatever
        if (null === $rft) {
            $inst->{$field} = $value;
            return;
        }

        $nullable = $rft->allowsNull() || Type::OBJECT === $rftName;

        // if the field is scalar, handle
        if (in_array((string)$rft, Type::SCALAR, true)) {
            $inst->{$field} = static::unmarshalScalar($inst, $field, $value, $rftName, $nullable);
            return;
        }

        // if we cannot, just try to set.
        $inst->{$field} = $value;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $type
     * @param bool $nullable
     * @return bool|float|int|string
     */
    private static function buildScalarValue(string $field, $value, string $type, bool $nullable)
    {
        // if the incoming value is null...
        if (null === $value) {
            // ...and this field is nullable, just return null
            if ($nullable) {
                return null;
            } else {
                // otherwise, return zero val for this type
                return ZeroVal::forType($type, $value);
            }
        } elseif (Type::STRING === $type) {
            return (string)$value;
        } elseif (Type::INTEGER === $type) {
            return \intval($value, 10);
        } elseif (Type::DOUBLE === $type) {
            return (float)$value;
        } elseif (Type::BOOLEAN === $type) {
            return (bool)$value;
        } else {
            // if we fall down to here, default to try to set the value to whatever it happens to be.
            return $value;
        }
    }

    /**
     * @param string $field
     * @param array|object $value
     * @param string $class
     * @param bool $nullable
     * @return object|null
     */
    private static function buildObjectValue(string $field, $value, string $class, bool $nullable): ?object
    {
        // if the incoming value is null...
        if (null === $value) {
            // ...and this field is nullable, return null
            if ($nullable) {
                return null;
            }
            // otherwise, check if class is registered with a custom zero state
            $zs = ZeroVal::$zeroStates->getClass($class);
            if (null !== $zs) {
                return $zs->zeroVal();
            }
            // otherwise, attempt to construct.
            // note, this will fail if the class's constructor has required parameters.  in these situations,
            // use either a custom unmarshaller func or register a custom zero val state
            return new $class();
        } elseif (method_exists($class, 'UnmarshalJSONDecoded')) {
            return $class::UnmarshalJSONDecoded((array)$value);
        } else {
            return new $class((array)$value);
        }
    }

    /**
     * Handles scalar type field hydration
     *
     * @param object $inst
     * @param string $field
     * @param mixed $value
     * @param string $fieldType
     * @param bool $nullable
     */
    private static function unmarshalScalar(object $inst, string $field, $value, $): void
    {
        $inst->{$field} = static::buildScalarValue(
            $field,
            $value,
            $fieldType,
            $nullable
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private static function unmarshalObject(object $inst, string $field, $value, array $def): void
    {
        if (!isset($def[Transcoding::FIELD_CLASS])) {
            throw new \LogicException(
                sprintf(
                    'Field "%s" on type "%s" is missing FIELD_CLASS hydration entry: %s',
                    $field,
                    static::class,
                    var_export($def, true)
                )
            );
        }

        static::{$field} = static::buildObjectValue(
            $field,
            $value,
            $def[Transcoding::FIELD_CLASS],
            self::fieldIsNullable($def)
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private static function unmarshalArray(string $field, $value, array $def): void
    {
        // attempt to extract the two possible keys
        $type  = $def[Transcoding::FIELD_ARRAY_TYPE] ?? null;
        $class = $def[Transcoding::FIELD_CLASS] ?? null;

        // type is required
        if (null === $type) {
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
                static::{$field} = null;
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

        if (Type::OBJECT === $type) {
            if (null === $class) {
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
                    static::{$field}[$k] = null;
                } else {
                    static::{$field}[$k] = static::buildObjectValue(
                        $field,
                        $v,
                        $class,
                        false
                    );
                }
            }
        } else {
            // in all other cases, just set as-is
            foreach ($value as $k => $v) {
                if (null === $v) {
                    static::{$field}[$k] = null;
                } else {
                    static::{$field}[$k] = static::buildScalarValue($field, $v, $type, false);
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
    private static function unmarshalFromTags(string $field, $value, array $def): void
    {
        // check if a callable has been defined
        if (isset($def[Transcoding::FIELD_UNMARSHAL_CALLBACK])) {
            $cb = $def[Transcoding::FIELD_UNMARSHAL_CALLBACK];
            // allow for using a "setter" method
            if (\is_string($cb) && method_exists($this, $cb)) {
                static::{$cb}($value);
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

        // try to determine field type by first looking up the field in the definition map, then by inspecting the
        // the field's default value.
        //
        // objects _must_ have an entry in the map, as they are either un-initialized at class instantiation time or
        // set to "NULL", at which point we cannot automatically determine the value type.

        if (isset($def[Transcoding::FIELD_TYPE])) {
            // if the field has a FIELD_TYPE value in the definition map
            $fieldType = $def[Transcoding::FIELD_TYPE];
        } elseif (isset(static::{$field})) {
            // if the field is set and non-null
            $fieldType = \gettype(static::{$field});
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
            static::unmarshalObject($field, $value, $def);
        } elseif (Type::ARRAY === $fieldType) {
            static::unmarshalArray($field, $value, $def);
        } else {
            // at this point, assume scalar
            // todo: handle non-scalar types here
            static::unmarshalScalar($field, $value, self::fieldIsNullable($def));
        }
    }
}
