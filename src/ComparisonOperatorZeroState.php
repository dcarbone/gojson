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
 * ComparisonOperatorZeroState uses the comparison operator, "==", to determine if the current state of an object
 * is equivalent to its zero-state value.
 */
class ComparisonOperatorZeroState implements ZeroState
{
    private ?object $_zeroVal;

    public function __construct(?object $zeroVal)
    {
        if (null === $zeroVal) {
            $this->_zeroVal = null;
        } else {
            $this->_zeroVal = clone $zeroVal;
        }
    }

    public function isZero(?object $value): bool
    {
        return $value == $this->_zeroVal;
    }

    public function zeroVal(): ?object
    {
        if (null === $this->_zeroVal) {
            return null;
        }
        return clone $this->_zeroVal;
    }
}