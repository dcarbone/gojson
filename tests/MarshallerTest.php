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

use DCarbone\Go\JSON\Tests\Types\TestArrayStringField;
use DCarbone\Go\JSON\Tests\Types\TestBooleanField;
use DCarbone\Go\JSON\Tests\Types\TestDoubleField;
use DCarbone\Go\JSON\Tests\Types\TestIntegerField;
use DCarbone\Go\JSON\Tests\Types\TestStringField;
use PHPUnit\Framework\TestCase;

class MarshallerTest extends TestCase
{
    protected function executeMarshalTest(object $inst, string $expectedJSON)
    {
        $this->assertIsObject($inst);
        $json = $inst->MarshalJSON();
        $this->assertIsString($json);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString($json, $expectedJSON);
    }

    public function testMarshal_String()
    {
        $this->executeMarshalTest(
            TestStringField::UnmarshalJSON('{"var":"value"}'),
            '{"var":"value"}'
        );
    }

    public function testMarshal_Integer()
    {
        $this->executeMarshalTest(
            TestIntegerField::UnmarshalJSON('{"var":1}'),
            '{"var":1}'
        );
    }

    public function testMarshal_Float()
    {
        $this->executeMarshalTest(
            TestDoubleField::UnmarshalJSON('{"var":1.1}'),
            '{"var":1.1}'
        );
    }

    public function testMarshal_Boolean()
    {
        $this->executeMarshalTest(
            TestBooleanField::UnmarshalJSON('{"var":true}'),
            '{"var":true}'
        );
    }

    public function testMarshal_Array_String()
    {
        $this->executeMarshalTest(
            TestArrayStringField::UnmarshalJSON('{"var":["one","two"]}'),
            '{"var":["one","two"]}'
        );
    }
}
