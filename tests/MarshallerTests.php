<?php

declare(strict_types=1);

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
    /**
     * @covers Marshaller::MarshalJSON
     * @return void
     */
    public function testMarshalFields()
    {
        $TESTS = [
            'string-field'  => [
                instanceT     => new TestStringField('value'),
                expectedT     => [
                    jsonT => '{"var":"value"}',
                ],
            ],
            'integer-field' => [
                instanceT     => new TestIntegerField(1),
                expectedT     => [
                    jsonT => '{"var":1}',
                ],
            ],
            'double-field'  => [
                instanceT     => new TestDoubleField(1.1),
                expectedT     => [
                    jsonT => '{"var":1.1}',
                ],
            ],
            'boolean-field' => [
                instanceT     => new TestBooleanField(true),
                expectedT     => [
                    jsonT => '{"var":true}',
                ],
            ],
        ];

        foreach ($TESTS as $name => $test) {
            $this->assertIsObject($test[instanceT]);
            $json = $test[instanceT]->MarshalJSON();
            $this->assertIsString($json);
            $this->assertJson($json);
            $this->assertJsonStringEqualsJsonString($json, $test[expectedT][jsonT]);
        }
    }
}
