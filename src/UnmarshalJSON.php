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

/**
 * Used to assist with unmarshalling json responses
 *
 * Trait Unmarshaller
 */
trait UnmarshalJSON
{
    /**s
     * @param string|null $json
     * @return static
     * @throws \ReflectionException
     */
    public static function UnmarshalJSON(?string $json): object
    {
        // construct new instance of containing class
        $zs = Zero::$zeroStates->getClass(static::class);
        if (null !== $zs) {
            $inst = $zs->zeroVal();
        } else {
            $rc   = new \ReflectionClass(static::class);
            $inst = $rc->newInstanceWithoutConstructor();
        }

        // if null is provided, return zero val of class
        if (null === $json || '' === $json) {
            return $inst;
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

        // otherwise, continue with unmarshalling
        $fieldsDefined = defined(static::class . '::FIELDS');
        $fieldDef      =  [];
        foreach ($decoded as $field => $value) {
            if ($fieldsDefined) {
                $fieldDef = static::FIELDS[$field] ?? null;
            }
            JSONUnmarshaller::unmarshalField($inst, $fieldDef, $field, $value);
        }
        return $inst;
    }
}
