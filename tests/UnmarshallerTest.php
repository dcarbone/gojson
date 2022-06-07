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

class UnmarshallerTest extends TestCase
{
    protected function executeUnmarshalFieldTest(string $class, string $srcJSON, object $expected)
    {
        $inst = $class::UnmarshalJSON($srcJSON);
        $this->assertInstanceOf($class, $inst);
        $this->assertObjectEquals($inst, $expected);
    }

    public function testUnmarshal_String()
    {
        $expected      = new TestStringField();
        $expected->var = 'value';
        $this->executeUnmarshalFieldTest(
            TestStringField::class,
            '{"var": "value"}',
            $expected
        );
    }

    public function testUnmarshal_Integer()
    {
        $expected      = new TestIntegerField();
        $expected->var = 1;
        $this->executeUnmarshalFieldTest(
            TestIntegerField::class,
            '{"var": 1}',
            $expected
        );
    }

    public function testUnmarshal_Float()
    {
        $expected      = new TestDoubleField();
        $expected->var = 1.1;
        $this->executeUnmarshalFieldTest(
            TestDoubleField::class,
            '{"var":1.1}',
            $expected
        );
    }

    public function testUnmarshal_Boolean()
    {
        $expected      = new TestBooleanField();
        $expected->var = true;
        $this->executeUnmarshalFieldTest(
            TestBooleanField::class,
            '{"var":true}',
            $expected
        );
    }

    public function testUnmarshal_Array_String()
    {
        $expected      = new TestArrayStringField();
        $expected->var = ["one", "two"];
        $this->executeUnmarshalFieldTest(
            TestArrayStringField::class,
            '{"var":["one","two"]}',
            $expected
        );
    }
}
