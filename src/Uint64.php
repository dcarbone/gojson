<?php

declare(strict_types=1);

namespace DCarbone\Go\JSON;

/*
   Source for this is based on https://www.siderea.nl/php/class.uint64.txt

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

class Uint64
{
    /*
        PHP class enabling handling of the unsigned (64-bit) integer type

        version: 1.2

        Requires 64-bit build of PHP

        Functions using Arithmetic Operators:
        addition +	-> Add64($a,$b) -> returns ($a + $b)
        subtract -	-> Sub64($a,$b)	-> returns ($a - $b)
        multiply *	-> Mul64($a,$b) -> returns ($a * $b)
        modulus  %	-> Mod64($a,$d)	-> returns ($a % $d)
        Returned integer mimics the 64-bit unsigned integer type

        Bitwise operators in PHP will do fine except for the bitwise right shift ($x >> $bits)
        For correct usage of bitwise right shifts in PHP do -> ($x >> $bits) & (PHP_INT_MAX >> ($bits - 1))

        Casting functions.
        str2int()	raw/binary (8 byte) string to integer
        int2str()	integer to raw/binary (8 byte) string
        hex2int()	16 character hex string to integer
        int2dec()	integer to 20 character unsigned decimal string
        int2hex()	integer to 16 character hex string

        NOT yet supported:
        - division operator (/)

        New functions soon to be added:
        - Add64carry  -> returns sum and carry (carry as a third function argument by reference)
        - Mul64.128   -> returning 128 bit unsigned integer as 2 element array (64 upper bits, 64 lower bits)
    */

    /**
     * @param string $str
     * @return int
     */
    public function str2int(string $str): int
    {
        $split = unpack('N2', $str);

        return ($split[1] << 32) | $split[2];
    }

    /**
     * @param int $int
     * @return string
     */
    public function int2str(int $int): string
    {
        return hex2bin(sprintf('%016x', $int));
    }

    /**
     * @param string $str
     * @return int
     */
    public function hex2int(string $str): int
    {
        $split = unpack('N2', hex2bin($str));

        return ($split[1] << 32) | $split[2];
    }

    /**
     * @param int $int
     * @return string
     */
    public function int2dec(int $int): string
    {
        return sprintf('%020u', $int);
    }

    public function int2hex($int): string
    {
        return sprintf('%016x', $int);
    }

    /**
     * @param $a
     * @param $d
     * @return int
     */
    public function Mod64($a, $d): int
    {
        if ($a < 0) {
            $mod = (($a & PHP_INT_MAX) % $d) + (PHP_INT_MAX % $d) + 1;

            if ($mod < $d) {
                return $mod;
            }

            return $mod - $d;
        }

        return $a % $d;
    }

    /**
     * @param $a
     * @param $b
     * @return int|string
     */
    public function Mul64($a, $b)
    {
        $min = ~PHP_INT_MAX;
        //		$min	= PHP_INT_MIN;

        $mask60 = 0x000000000000000f;
        $mask34 = 0x000000003fffffff;

        $aL = $a & $mask60;
        $bH = ($b >> 60) & $mask60;

        $bL = $b & $mask60;
        $aH = ($a >> 60) & $mask60;

        $La = $a & $mask34;
        $Ha = ($a >> 30) & $mask34;

        $Lb = $b & $mask34;
        $Hb = ($b >> 30) & $mask34;

        $sum1 = (($La * $Hb) + ($Ha * $Lb)) << 30;
        $sum2 = ((($Ha * $Hb) + ($aL * $bH) + ($bL * $aH)) << 60) | ($La * $Lb);

        //
        $sum = $min + ($sum1 & PHP_INT_MAX) + ($sum2 & PHP_INT_MAX);

        if (!(($sum1 ^ $sum2) < 0)) {
            $sum ^= $min;
        }

        return $sum;
    }

    /**
     * @param int $a
     * @param int $b
     * @return int|string
     */
    public function Add64(int $a, int $b)
    {
        $min = ~PHP_INT_MAX;
        //		$min	= PHP_INT_MIN;

        $sum = $min + ($a & PHP_INT_MAX) + ($b & PHP_INT_MAX);

        if (($a ^ $b) < 0) {
            return $sum;
        }

        return $sum ^ $min;
    }

    public function Sub64($a, $b)
    {
        $min = ~PHP_INT_MAX;
        //		$min	= PHP_INT_MIN;

        if ($a < 0) {
            $a &= PHP_INT_MAX;

            if ($b < 0) {
                $b &= PHP_INT_MAX;

                if ($a < $b) {
                    return $min + (PHP_INT_MAX - $b) + $a + 1;
                } else {
                    return $a - $b;
                }
            } elseif ($a < $b) {
                return ($min + (PHP_INT_MAX - $b) + $a + 1) ^ $min;
            } else {
                return ($a - $b) ^ $min;
            }
        } elseif ($b < 0) {
            $b &= PHP_INT_MAX;

            if ($a < $b) {
                return ($min + (PHP_INT_MAX - $b) + $a + 1) ^ $min;
            } else {
                return ($a - $b) ^ $min;
            }
        } elseif ($a < $b) {
            return $min + (PHP_INT_MAX - $b) + $a + 1;
        } else {
            return $a - $b;
        }
    }
}
