<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Copyright 2023 Daniel Carbone (daniel.p.carbone@gmail.com)

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

interface ZeroVal
{
    /**
     * Must return true if the current state of this object is "zero"
     *
     * @return bool
     */
    public function isZero(): bool;

    /**
     * Must return the zero value of this object type
     *
     * @return ?object
     */
    public function zeroVal(): ?object;
}
