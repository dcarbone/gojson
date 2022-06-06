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
use DCarbone\Go\JSON\Type;
use PHPUnit\Framework\TestCase;

class UnmarshallerTests extends TestCase
{
    private const TESTS = [
        'string-field'  => [
            classT    => TestStringField::class,
            jsonT     => '{"var": "value"}',
            expectedT => [
                fieldT => varT,
                typeT  => Type::STRING,
                valueT => valueT,
            ],
        ],
        'integer-field' => [
            classT    => TestIntegerField::class,
            jsonT     => '{"var": 1}',
            expectedT => [
                fieldT => varT,
                typeT  => Type::INTEGER,
                valueT => 1,
            ],
        ],
        'double-field'  => [
            classT    => TestDoubleField::class,
            jsonT     => '{"var": 1.1}',
            expectedT => [
                fieldT => varT,
                typeT  => Type::DOUBLE,
                valueT => 1.1,
            ],
        ],
        'boolean-field' => [
            classT    => TestBooleanField::class,
            jsonT     => '{"var": true}',
            expectedT => [
                fieldT => varT,
                typeT  => Type::BOOLEAN,
                valueT => true,
            ],
        ],
    ];

    /**
     * @covers Unmarshaller::UnmarshalJSON
     * @return void
     */
    public function testUnmarshalFields()
    {
        foreach (self::TESTS as $name => $test) {
            $inst = $test[classT]::UnmarshalJSON($test[jsonT]);
            $this->assertInstanceOf($test[classT], $inst);
            $this->assertObjectHasAttribute($test[expectedT][fieldT], $inst);
            $fieldValue = $inst->{$test[expectedT][fieldT]};
            switch ($test[expectedT][typeT]) {
                case Type::STRING:
                    $this->assertIsString($fieldValue);
                    break;
                case Type::INTEGER:
                    $this->assertIsInt($fieldValue);
                    break;
                case Type::DOUBLE:
                    $this->assertIsFloat($fieldValue);
                    break;
                case Type::BOOLEAN:
                    $this->assertIsBool($fieldValue);
                    break;
                case Type::OBJECT:
                    $this->assertIsObject($fieldValue);
                    $this->assertInstanceOf($test[expectedT][fieldClassT], $fieldValue);
                    break;
                case Type::ARRAY:
                    $this->assertIsArray($fieldValue);
                    break;

                default:
                    throw new \UnexpectedValueException(
                        sprintf(
                            'Unhandled test case: %s',
                            var_export($test, true)
                        )
                    );
            }
            $this->assertEquals($test[expectedT][valueT], $fieldValue);
        }
    }
}
