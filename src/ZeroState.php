<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Copyright 2021-2023 Daniel Carbone (daniel.p.carbone@gmail.com)

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

interface ZeroState
{
    /**
     * Must return true if provided value is considered "zero"
     *
     * @param object $value
     * @return bool
     */
    public function isZero(object $value): bool;

    /**
     * Must return an example of an "empty" value of this type
     *
     * @return mixed
     */
    public function zeroVal(): mixed;
}
