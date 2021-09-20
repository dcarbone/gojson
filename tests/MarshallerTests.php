<?php declare(strict_types=1);

namespace DCarbone\Go\JSON\Tests;

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

use DCarbone\Go\JSON\Tests\Types\TestBooleanField;
use DCarbone\Go\JSON\Tests\Types\TestDoubleField;
use DCarbone\Go\JSON\Tests\Types\TestIntegerField;
use DCarbone\Go\JSON\Tests\Types\TestStringField;
use PHPUnit\Framework\TestCase;

class MarshallerTests extends TestCase
{
    public function testMarshalFields()
    {
        $TESTS = [
            'string-field'  => [
                'inst'     => TestStringField::UnmarshalJSON('{"var":"value"}'),
                'expected' => [
                    'json' => '{"var":"value"}',
                ],
            ],
            'integer-field' => [
                'inst'     => TestIntegerField::UnmarshalJSON('{"var":1}'),
                'expected' => [
                    'json' => '{"var":1}',
                ],
            ],
            'double-field'  => [
                'inst'     => TestDoubleField::UnmarshalJSON('{"var":1.1}'),
                'expected' => [
                    'json' => '{"var":1.1}',
                ],
            ],
            'boolean-field' => [
                'inst'     => TestBooleanField::UnmarshalJSON('{"var":true}'),
                'expected' => [
                    'json' => '{"var":true}',
                ],
            ],
        ];

        foreach ($TESTS as $name => $test) {
            $this->assertIsObject($test['inst']);
            $json = $test['inst']->MarshalJSON();
            $this->assertIsString($json);
            $this->assertJson($json);
            $this->assertJsonStringEqualsJsonString($json, $test['expected']['json']);
        }
    }
}