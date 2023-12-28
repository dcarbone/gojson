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

class Error
{
    /**
     * Human-readable error message
     *
     * @var string
     */
    public string $message;

    /**
     * @param string $message Human readable error message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @param string $f Format string
     * @param ...$v $values
     * @return \DCarbone\Go\JSON\Error
     */
    public static function Errorf(string $f, ...$v): Error
    {
        return new self(sprintf($f, ...$v));
    }

    /**
     * @return string
     */
    public function Error(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }
}
