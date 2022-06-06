<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON\Tests\Types;

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

use DCarbone\Go\JSON\Field;
use DCarbone\Go\JSON\Marshaller;
use DCarbone\Go\JSON\Type;
use DCarbone\Go\JSON\Unmarshaller;
use DCarbone\Go\JSON\Zero;

class TestDoubleField
{
    use Marshaller;
    use Unmarshaller;

    protected const FIELDS = [
        'var' => [
            Field::TYPE => Type::DOUBLE,
        ],
    ];

    public float $var;

    public function __construct(float $var = Zero::DOUBLE)
    {
        $this->var = $var;
    }
}
