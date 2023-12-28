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

function UnmarshalJSON(string $json, &$any, int $opts = JSON_BIGINT_AS_STRING): ?Error
{
    if ($any === null) {
        return new Error('$any must not be null');
    }

    if (0 === strlen($json)) {
        return new Error('$json must not be empty');
    }

    $decoded = json_decode($json, false);
    $err     = json_last_error();
    if (JSON_ERROR_NONE !== $err) {
        return new Error(json_last_error_msg());
    }

    if (Type::IsScalar($decoded)) {
        return $any = $decoded;
    }




    if ($any instanceof Any) {

    }

    if (Type::OBJECT === $dt && $dt instanceof Any) {
        if (Type::P)
    }
}
