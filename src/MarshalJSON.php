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

trait MarshalJSON
{
    /**
     * @param int $jsonEncodeFlags
     * @return string
     */
    public function MarshalJSON(int $jsonEncodeFlags = JSON_UNESCAPED_SLASHES): string
    {
        $out = [];
        foreach ((array)$this as $field => $value) {
            // if this field has no special handling, set as-is and move on.
            if (!defined(static::class . '::FIELDS') || !isset(static::FIELDS[$field])) {
                $out[$field] = $value;
            } else {
                JSONMarshallerImpl::marshalField($out, $this, $field, $value, static::FIELDS[$field]);
            }
        }
        return json_encode($out, $jsonEncodeFlags);
    }
}
