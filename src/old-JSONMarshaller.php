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

abstract class JSONMarshaller
{
    /**
     * Marshal field is designed to replicate (to ao point) what Golang does during the json.Marshal call
     *
     * @param array $output
     * @param object $inst
     * @param string $field
     * @param mixed $value
     * @param array $fieldDef
     */
    public static function marshalField(array &$output, object $inst, string $field, $value, array $fieldDef): void
    {
        // if this field is marked as skipped, move on
        if (isset($fieldDef[Transcoding::FIELD_MARSHAL_SKIP]) && true === $fieldDef[Transcoding::FIELD_MARSHAL_SKIP]) {
            return;
        }

        // if a marshal callback is defined, defer to that and move on.
        if (isset($fieldDef[Transcoding::FIELD_MARSHAL_CALLBACK])) {
            $cb = $fieldDef[Transcoding::FIELD_MARSHAL_CALLBACK];
            if (false === \call_user_func($cb, $inst, $field, $value)) {
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

        // if this field is NOT explicitly marked as "omitempty", set and move on.
        if (!isset($fieldDef[Transcoding::FIELD_OMITEMPTY]) || true !== $fieldDef[Transcoding::FIELD_OMITEMPTY]) {
            $output[$field] = $value;
            return;
        }

        // otherwise, handle value setting on a per-type basis
        if (!ZeroVal::isZero($value)) {
            $output[$field] = $value;
        }
    }
}
