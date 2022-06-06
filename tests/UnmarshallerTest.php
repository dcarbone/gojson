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
use DCarbone\Go\JSON\Transcoding;
use PHPUnit\Framework\TestCase;

class UnmarshallerTest extends TestCase
{
    private const TESTS = [
        'string-field' => [
            'class'    => TestStringField::class,
            'json'     => // language=JSON
                <<<EOT
{"var": "value"}
EOT,
            'expected' => [
                'field' => 'var',
                'type'  => Transcoding::STRING,
                'value' => 'value',
            ],
        ],
        'integer-field' => [
            'class'    => TestIntegerField::class,
            'json'     => // language=JSON
                <<<EOT
{"var": 1}
EOT,
            'expected' => [
                'field' => 'var',
                'type'  => Transcoding::INTEGER,
                'value' => 1,
            ],
        ],
        'double-field' => [
            'class'    => TestDoubleField::class,
            'json'     => // language=JSON
                <<<EOT
{"var": 1.1}
EOT,
            'expected' => [
                'field' => 'var',
                'type'  => Transcoding::DOUBLE,
                'value' => 1.1,
            ],
        ],
        'boolean-field' => [
            'class'    => TestBooleanField::class,
            'json'     => // language=JSON
                <<<EOT
{"var": true}
EOT,
            'expected' => [
                'field' => 'var',
                'type'  => Transcoding::BOOLEAN,
                'value' => true,
            ],
        ],
    ];

    public function testUnmarshalFields()
    {
        foreach (self::TESTS as $name => $test) {
            $inst = $test['class']::UnmarshalJSON($test['json']);
            $this->assertInstanceOf($test['class'], $inst);
            $this->assertObjectHasAttribute($test['expected']['field'], $inst);
            $fieldValue = $inst->{$test['expected']['field']};
            switch ($test['expected']['type']) {
                case Transcoding::STRING:
                    $this->assertIsString($fieldValue);
                    break;
                case Transcoding::INTEGER:
                    $this->assertIsInt($fieldValue);
                    break;
                case Transcoding::DOUBLE:
                    $this->assertIsFloat($fieldValue);
                    break;
                case Transcoding::BOOLEAN:
                    $this->assertIsBool($fieldValue);
                    break;
                case Transcoding::OBJECT:
                    $this->assertIsObject($fieldValue);
                    $this->assertInstanceOf($test['expected']['field_class'], $fieldValue);
                    break;
                case Transcoding::ARRAY:
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
        }
    }
}
