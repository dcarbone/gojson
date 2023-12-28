<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Copyright 2021-2023 Daniel Carbone (daniel.p.carbone@gmail.com)

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

class ZeroStates
{
    /** @var \DCarbone\Go\JSON\ZeroState[] */
    private array $_states = [];

    /**
     * @param string|object $class
     * @param \DCarbone\Go\JSON\ZeroState $state
     * @return void
     */
    public function setState(string|object $class, ZeroState $state): void
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }
        if (!is_string($class)) {
            throw new \InvalidArgumentException(sprintf('Value for $class must be instance of object or class name, saw %s', gettype($class)));
        }
        $this->_states[$class] = $state;
    }

    /**
     * @param string|object $class
     * @param object|null $zeroVal
     * @return void
     */
    public function setStateValue(string|object $class, ?object $zeroVal): void
    {
        $this->setState($class, new ComparisonOperatorZeroState($zeroVal));
    }

    /**
     * @param string|object $class
     * @return \DCarbone\Go\JSON\ZeroState|null
     */
    public function getZeroState(string|object $class): ?ZeroState
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }
        if (!is_string($class)) {
            throw new \InvalidArgumentException(sprintf('Value for $class must be instance of object or class name, saw %s', gettype($class)));
        }
        return $this->_states[$class] ?? null;
    }
}
