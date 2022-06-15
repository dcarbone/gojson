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
 * Class Transcoding
 */
final class Transcoding
{
    //-- common field type definitions with omitempty

    public const OMITEMPTY_FIELD = [Field::OMITEMPTY => true];

    public const OMITEMPTY_STRING_FIELD        = [Field::TYPE => Type::STRING] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_INTEGER_FIELD       = [Field::TYPE => Type::INTEGER] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_DOUBLE_FIELD        = [Field::TYPE => Type::DOUBLE] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_BOOLEAN_FIELD       = [Field::TYPE => Type::BOOLEAN] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_STRING_ARRAY_FIELD  = [
        Field::TYPE       => Type::ARRAY,
        Field::ARRAY_TYPE => Type::STRING,
    ] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_INTEGER_ARRAY_FIELD = [
        Field::TYPE       => Type::ARRAY,
        Field::ARRAY_TYPE => Type::INTEGER,
    ] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_FLOAT_ARRAY_FIELD = [
        Field::TYPE       => Type::ARRAY,
        Field::ARRAY_TYPE => Type::FLOAT,
    ] + self::OMITEMPTY_FIELD;
    public const OMITEMPTY_BOOLEAN_ARRAY_FIELD = [
        Field::TYPE       => Type::ARRAY,
        Field::ARRAY_TYPE => Type::BOOLEAN,
    ] + self::OMITEMPTY_FIELD;
}
