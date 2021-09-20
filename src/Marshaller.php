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

use DCarbone\Go\Time;

/**
 * Used to assist with marshalling types into json
 *
 * Trait Marshaller
 */
trait Marshaller
{
    /**
     * @param int $flags
     * @return string
     */
    public function MarshalJSON(int $flags = JSON_UNESCAPED_SLASHES): string {
        $out = [];
        foreach ((array)$this as $field => $value) {
            $this->marshalField($out, $field, $value);
        }
        return json_encode($out, $flags);
    }

    /**
     * Marshal field is designed to replicate (to ao point) what Golang does during the json.Marshal call
     *
     * @param array $output
     * @param string $field
     * @param mixed $value
     */
    protected function marshalField(array &$output, string $field, $value): void
    {
        // if this field has no special handling, set as-is and move on.
        if (!defined(static::class . '::FIELDS') || !isset(static::FIELDS[$field])) {
            $output[$field] = $value;
            return;
        }

        // otherwise, get definition
        $def = static::FIELDS[$field];

        // if this field is marked as skipped, move on
        if (isset($def[Transcoding::FIELD_MARSHAL_SKIP]) && true === $def[Transcoding::FIELD_MARSHAL_SKIP]) {
            return;
        }

        // if a marshal callback is defined, defer to that and move on.
        if (isset($def[Transcoding::FIELD_MARSHAL_CALLBACK])) {
            $cb = $def[Transcoding::FIELD_MARSHAL_CALLBACK];
            if (false === \call_user_func($cb, $this, $field, $value)) {
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
        if (isset($def[Transcoding::FIELD_MARSHAL_AS])) {
            switch ($def[Transcoding::FIELD_MARSHAL_AS]) {
                case Transcoding::STRING:
                    $value = (string)$value;
                    break;
                case Transcoding::INTEGER:
                    $value = (int)$value;
                    break;
                case Transcoding::DOUBLE:
                    $value = (float)$value;
                    break;
                case Transcoding::BOOLEAN:
                    $value = (bool)$value;
                    break;

                default:
                    throw new \InvalidArgumentException(
                        sprintf('Unable to handle serializing to %s', $def[Transcoding::FIELD_MARSHAL_AS])
                    );
            }
        }

        // if this field is NOT explicitly marked as "omitempty", set and move on.
        if (!isset($def[Transcoding::FIELD_OMITEMPTY]) || true !== $def[Transcoding::FIELD_OMITEMPTY]) {
            $output[$field] = $value;
            return;
        }

        // otherwise, handle value setting on a per-type basis

        $type = \gettype($value);

        if (Transcoding::STRING === $type) {
            // strings must be non empty
            if ('' !== $value) {
                $output[$field] = $value;
            }
        } elseif (Transcoding::INTEGER === $type) {
            // integers must be non-zero (negatives are ok)
            if (0 !== $value) {
                $output[$field] = $value;
            }
        } elseif (Transcoding::DOUBLE === $type) {
            // floats must be non-zero (negatives are ok)
            if (0.0 !== $value) {
                $output[$field] = $value;
            }
        } elseif (Transcoding::BOOLEAN === $type) {
            // bools must be true
            if ($value) {
                $output[$field] = $value;
            }
        } elseif (Transcoding::OBJECT === $type) {
            // object "non-zero" calculations require a bit more finesse...
            if ($value instanceof \Countable) {
                // countable types are non-empty if length > 0
                if (0 < \count($value)) {
                    $output[$field] = $value;
                }
            } elseif ($value instanceof Time\Duration) {
                // Time\Duration types are non-zero if their internal value is > 0
                if (0 < $value->Nanoseconds()) {
                    $output[$field] = $value;
                }
            } elseif ($value instanceof Time\Time) {
                // Time\Time values are non-zero if they are anything greater than epoch
                if (!$value->IsZero()) {
                    $output[$field] = $value;
                }
            } else {
                // otherwise, by being defined it is non-zero, so add it.
                $output[$field] = $value;
            }
        } elseif (Transcoding::ARRAY === $type) {
            // arrays must have at least 1 value
            if ([] !== $value) {
                $output[$field] = $value;
            }
        } elseif (Transcoding::RESOURCE === $type) {
            // todo: be more better about resources
            $output[$field] = $value;
            return;
        }

        // once we get here the only possible value type is "NULL", which are always considered "empty".  thus, do not
        // set any value.
    }
}
