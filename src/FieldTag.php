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

interface FieldTag
{
    public const OMITEMPTY          = 'omitempty';
    public const UNMARSHAL_CALLBACK = 'unmarshalCallback';
    public const MARSHAL_CALLBACK   = 'marshalCallback';
    public const SKIP               = 'skip';
    public const JSON_NAME          = 'jsonName';
    public const ARRAY_TYPE         = 'arrayType';

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param mixed $value
     * @return \DCarbone\Go\JSON\MarshalResult
     */
    public function marshal($value): MarshalResult;

    /**
     * @param mixed $value
     * @return \DCarbone\Go\JSON\UnmarshalResult
     */
    public function unmarshal($value): UnmarshalResult;
}
