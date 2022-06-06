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

class Zero
{
    public const STRING  = '';
    public const INTEGER = 0;
    public const DOUBLE  = 0.0;
    public const FLOAT   = self::DOUBLE;
    public const BOOLEAN = false;
    public const ARRAY   = [];
    public const OBJECT  = null;

    /** @var \DCarbone\Go\JSON\ZeroStates */
    public static ZeroStates $zeroStates;

    /**
     * @param string $type
     * @return bool|float|int|string|null
     */
    public static function forType(string $type): bool|float|int|string|null
    {
        return match ($type) {
            Type::STRING => Zero::STRING,
            Type::INTEGER => Zero::INTEGER,
            Type::DOUBLE => Zero::DOUBLE,
            Type::BOOLEAN => Zero::BOOLEAN,
            default => null,
        };
    }

    /**
     * TODO: provide test for whether zero after array_filter?
     * TODO: resources will currently always return non-zero.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isZero(mixed $value): bool
    {
        // NULL and empty array are always zero'
        if (null === $value || [] === $value) {
            return true;
        }

        $type = \gettype($value);

        if (Type::STRING === $type) {
            return Zero::STRING === $value;
        } elseif (Type::INTEGER === $type) {
            return Zero::INTEGER === $value;
        } elseif (Type::DOUBLE === $type) {
            return Zero::DOUBLE === $value;
        } elseif (Type::BOOLEAN === $type) {
            return Zero::BOOLEAN === $value;
        } elseif (Type::OBJECT === $type) {
            if ($value instanceof \Countable) {
                return 0 == \count($value);
            } elseif (null !== ($zs = static::$zeroStates->getClass($value))) {
                return $zs->isZero($value);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

// TODO: dirty and i hate it.
if (!isset(Zero::$zeroStates)) {
    Zero::$zeroStates = new ZeroStates();
}
