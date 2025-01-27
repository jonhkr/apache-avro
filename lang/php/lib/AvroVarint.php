<?php

/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Apache\Avro;

/**
 * Varint encoding/decoding for 64bit php
 *
 * @package Avro
 */
class AvroVarint 
{
    public static function encodeLong(int $n): string 
    {
        if ($n >= 0 && $n < 0x80) {
            return chr($n);
        }

        $buf = [];
        if (($n & ~0x7F) != 0) {
            $buf[] = ($n | 0x80) & 0xFF;
            $n = ($n >> 7) ^ (($n >> 63) << 57); // unsigned shift right ($n >>> 7)

            while ($n > 0x7F) {
                $buf[] = ($n | 0x80) & 0xFF;
                $n >>= 7; // $n is always positive here
            }
        }

        $buf[] = $n;
        return pack("C*", ...$buf);
    }

    public static function decodeLong(array $bytes): int 
    {
        $b = array_shift($bytes);
        $n = $b & 0x7f;
        $shift = 7;
        while (0 != ($b & 0x80)) {
            $b = array_shift($bytes);
            $n |= (($b & 0x7f) << $shift);
            $shift += 7;
        }
        return ($n >> 7) ^ (($n >> 63) << 57) ^ -($n & 1);
    }
}
