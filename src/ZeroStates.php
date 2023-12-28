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

class ZeroStates
{
    /** @var \DCarbone\Go\JSON\ZeroStateInterface[] */
    private array $states = [];

    public function __construct()
    {
    }

    /**
     * @param string|object $class
     * @param \DCarbone\Go\JSON\ZeroStateInterface $state
     * @return void
     */
    public function addClass(string|object $class, ZeroStateInterface $state): void
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }
        if (!is_string($class)) {
            throw new \InvalidArgumentException(sprintf('Value for $class must be instance of object or class name, saw %s', gettype($class)));
        }
        $this->states[$class] = $state;
    }

    /**
     * @param string|object $class
     * @return \DCarbone\Go\JSON\ZeroStateInterface|null
     */
    public function getClass(string|object $class): ?ZeroStateInterface
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }
        if (!is_string($class)) {
            throw new \InvalidArgumentException(sprintf('Value for $class must be instance of object or class name, saw %s', gettype($class)));
        }
        return $this->states[$class] ?? null;
    }
}
