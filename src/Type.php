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

class Type
{
    public const STRING   = 'string';
    public const INTEGER  = 'integer';
    public const DOUBLE   = 'double';
    public const FLOAT    = self::DOUBLE;
    public const BOOLEAN  = 'boolean';
    public const OBJECT   = 'object';
    public const ARRAY    = 'array';
    public const RESOURCE = 'resource';
    public const MIXED    = 'mixed';
    public const NULL     = 'NULL';

    public const SCALAR = [self::STRING, self::INTEGER, self::DOUBLE, self::BOOLEAN];
}
