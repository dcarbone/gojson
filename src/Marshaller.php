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
 * Used to assist with marshalling types into json
 */
trait Marshaller
{
    private static string $COLON_COLON_FIELDS = '::FIELDS';

    /**
     * Convenience method to be called within a typical `jsonSerialize` method
     *
     * @param int $flags
     * @return string
     */
    public function MarshalJSON(int $flags = JSON_UNESCAPED_SLASHES): string
    {
        // stores final json-serializable representation
        $output = [];
        // check if class has any special field handling at all
        $hasDefs = \defined(static::class . self::$COLON_COLON_FIELDS);
        // iterate over each field present on class
        foreach ((array)$this as $field => $value) {
            // if this class has no special field handling at all or for this field, set
            // as-is and move on
            if (!$hasDefs || !isset(static::FIELDS[$field])) {
                $output[$field] = $value;
            } else {
                $this->marshalField($output, $field, $value, static::FIELDS[$field]);
            }
        }
        return json_encode($output, $flags);
    }

    /**
     * Marshal field is designed to replicate (to ao point) what Golang does during the json.Marshal call
     *
     * @param array $output
     * @param string $field
     * @param mixed $value
     * @param array|null $def
     */
    protected function marshalField(array &$output, string $field, mixed $value, ?array $def): void
    {
        // quick check to see if this field is skipped
        if (isset($def[Field::MARSHAL_SKIP]) &&
            true === $def[Field::MARSHAL_SKIP]) {
            return;
        }

        // determined marshalled field name
        $name = $def[Field::JSON_NAME] ?? $field;

        // if a marshal callback is defined, defer to that and move on.
        if (isset($def[Field::MARSHAL_CALLBACK])) {
            $cb = $def[Field::MARSHAL_CALLBACK];
            // allow for using a class method for marshalled value
            if (\is_string($cb) && \method_exists($this, $cb)) {
                $output[$name] = $this->{$cb}($value);
                return;
            }
            // handle all other callable output
            if (false === \call_user_func($cb, $this, $field, $value, $name)) {
                throw new \RuntimeException(
                    sprintf(
                        'Error calling marshal callback "%s" for field "%s" on class "%s"',
                        var_export($cb, true),
                        $field,
                        static::class
                    )
                );
            }
            return;
        }

        // if this field is marked as needing to be typecast to a specific type for output
        if (isset($def[Field::MARSHAL_AS])) {
            $value = match ($def[Field::MARSHAL_AS]) {
                Type::STRING  => (string)$value,
                Type::INTEGER => (int)$value,
                Type::DOUBLE  => (float)$value,
                Type::BOOLEAN => (bool)$value,
                default       => throw new \InvalidArgumentException(
                    sprintf('Unable to handle serializing to %s', $def[Field::MARSHAL_AS])
                ),
            };
        }

        // if this field is NOT explicitly marked as "omitempty", set and move on.
        if (!isset($def[Field::OMITEMPTY]) || true !== $def[Field::OMITEMPTY]) {
            $output[$name] = $value;
            return;
        }

        // otherwise, determine whether value equates to 'zero' before setting
        if (!Zero::isZero($value)) {
            $output[$name] = $value;
        }
    }
}
