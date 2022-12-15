<?php declare(strict_types=1);
/**
 * HyperPlug
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace modethirteen\Http;

use Closure;

/**
 * Class StringUtil
 *
 * @package modethirteen\Http
 */
class StringUtil {

    public static function endsWith(string $haystack, string $needle) : bool {
        $length = strlen($needle);
        $start = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }

     public static function endsWithInvariantCase(string $haystack, string $needle) : bool {
        return self::endsWith(strtolower($haystack), strtolower($needle));
    }

    public static function isNullOrEmpty(?string $string) : bool {
        return $string === null || $string === '';
    }

    public static function startsWith(string $haystack, string $needle) : bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function startsWithInvariantCase(string $haystack, string $needle) : bool {
        return self::startsWith(strtolower($haystack), strtolower($needle));
    }

    /**
     * Stringify any value
     */
    public static function stringify(mixed $value) : string {
        if($value === null) {
            return '';
        }
        if(is_string($value)) {
            return $value;
        }
        if(is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if(is_array($value)) {
            return implode(',', array_map('strval', $value));
        }
        if($value instanceof Closure) {
            return strval($value());
        }
        return strval($value);
    }
}
