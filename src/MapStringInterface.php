<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

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

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

/**
 * Class FakeMap
 *
 * This class is currently used as a catch-all for instances where a "map[string]X" is used.
 */
class MapStringInterface implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable
{
    /** @var array */
    private array $_keys = [];
    /** @var array */
    private array $_values = [];

    /**
     * Map constructor.
     * @param array|null $data
     */
    public function __construct(?array $data = [])
    {
        if (null === $data || [] === $data) {
            return;
        }
        foreach ($data as $k => $v) {
            if (!is_string($k)) {
                throw new \UnexpectedValueException(sprintf('string key expected, saw "%s"', gettype($k)));
            }
            $this->_keys[] = $k;
            if (is_array($v) && count($v) > 0 && is_string(key($v))) {
                $this->_values[] = new MapStringInterface($v);
            } else {
                $this->_values[] = $v;
            }
        }
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->_keys);
    }

    public function next(): void
    {
        next($this->_keys);
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->_keys);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return null !== key($this->_keys);
    }

    public function rewind(): void
    {
        reset($this->_keys);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return in_array($offset, $this->_keys, true);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $idx = array_search($offset, $this->_keys, true);
        if (false === $idx) {
            throw new \OutOfBoundsException(sprintf('Offset "%s" does not exist', $offset));
        }
        return $this->_values[$idx];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(sprintf('$offset must be string, saw "%s"', gettype($offset)));
        }
        $idx = array_search($offset, $this->_keys, true);
        if (false === $idx) {
            $this->_keys[]   = $offset;
            $this->_values[] = $value;
        } else {
            $this->_keys[$idx]   = $offset;
            $this->_values[$idx] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(sprintf('$offset must be string, saw "%s"', gettype($offset)));
        }
        $idx = array_search($offset, $this->_keys, true);
        if (false === $idx) {
            return;
        }
        unset($this->_keys[$offset], $this->_values[$offset]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->_keys);
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        $c = new \stdClass();
        foreach ($this->_keys as $i => $k) {
            $c->{$k} = $this->_values[$i];
        }
        return $c;
    }
}
