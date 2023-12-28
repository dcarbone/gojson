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

class ZeroVal
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
     * @param mixed $value
     * @return mixed
     */
    public static function forType(string $type, mixed $value): mixed
    {
        switch ($type) {
            case Type::STRING:
                return ZeroVal::STRING;
            case Type::INTEGER:
                return ZeroVal::INTEGER;
            case Type::DOUBLE:
                return ZeroVal::DOUBLE;
            case Type::BOOLEAN:
                return ZeroVal::BOOLEAN;
            case Type::ARRAY:
                return ZeroVal::ARRAY;

            case Type::OBJECT:
                $zs = self::$zeroStates->getClass($value);
                if (null === $zs) {
                    return ZeroVal::OBJECT;
                }
                return $zs->zeroVal();

            case Type::RESOURCE:
                return null;

            default:
                throw new \UnexpectedValueException(sprintf('Zero val for type "%s" is not defined', $type));
        }
    }

    /**
     * TODO: resources will currently always return non-zero.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isZero(mixed $value): bool
    {
        // NULL and empty array are always zero
        if (null === $value || [] === $value) {
            return true;
        }

        $type = \gettype($value);

        if (Type::STRING === $type) {
            return ZeroVal::STRING === $value;
        } elseif (Type::INTEGER === $type) {
            return ZeroVal::INTEGER === $value;
        } elseif (Type::DOUBLE === $type) {
            return ZeroVal::DOUBLE === $value;
        } elseif (Type::BOOLEAN === $type) {
            return ZeroVal::BOOLEAN === $value;
        } elseif (Type::OBJECT === $type) {
            if ($value instanceof ZeroValInterface) {
                return $value->isZero();
            } elseif (null !== ($zs = static::$zeroStates->getClass($value))) {
                return $zs->isZero($value);
            } elseif ($value instanceof \Countable) {
                return 0 === \count($value);
            } else {
                return [] === \get_object_vars($value);
            }
        } else {
            return false;
        }
    }
}

if (!isset(ZeroVal::$zeroStates)) {
    ZeroVal::$zeroStates = new ZeroStates();
}
