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
namespace modethirteen\Http\Content;

use modethirteen\Http\StringUtil;

/**
 * Class ContentType
 *
 * @package modethirteen\Http\Content
 */
class ContentType implements \Stringable {
    final const JSON = 'application/json; charset=utf-8';
    final const CSS = 'text/css; charset=utf-8';
    final const JAVASCRIPT = 'application/javascript; charset=utf-8';
    final const HTML = 'text/html; charset=utf-8';
    final const XML = 'application/xml; charset=utf-8';
    final const TEXT = 'text/plain; charset=utf-8';
    final const STREAM = 'application/octet-stream';
    final const FORM_MULTIPART = 'multipart/form-data';
    final const FORM_URLENCODED = 'application/x-www-form-urlencoded';
    final const PHP = 'application/php; charset=utf-8';

    /**
     * Return a new ContentType instance from a full content-type string (ex: text/html; charset=utf-8)
     *
     * @return static|null
     */
    public static function newFromString(string $string) : ?object {
        $parts = array_map('trim', explode(';', $string));
        $typeParts = array_filter(explode('/', $parts[0], 2));
        if(count($typeParts) !== 2) {
            return null;
        }
        $mainType = !is_string($typeParts[0]) ? strval($typeParts[0]) : $typeParts[0];
        $subType = !is_string($typeParts[1]) ? strval($typeParts[1]) : $typeParts[1];
        $parameters = [];
        array_shift($parts);
        foreach($parts as $part) {
            if(!StringUtil::isNullOrEmpty($part)) {
                if(!str_contains($part, '=')) {
                    $k = $part;
                    $v = null;
                } else {
                    [$k, $v] = explode('=', $part);
                }
                $parameters[$k] = $v ?? '';
            }
        }
        return new static($mainType, $subType, $parameters);
    }

    /**
     * @var string
     */
    private readonly string $mainType;

    /**
     * @var string[]
     */
    private array $parameters = [];

    /**
     * @var string
     */
    private readonly string $subType;

    /**
     * @param string $mainType - main part of content-type header line (ex: application)
     * @param string $subType - sub type of content-type header line (ex: json)
     * @param string[] $parameters - key value pairs of parameters (ex: ['charset' => 'utf-8']
     */
    final public function __construct(string $mainType, string $subType, array $parameters = []) {
        $this->mainType = strtolower($mainType);
        $this->subType = strtolower($subType);
        foreach($parameters as $parameter => $value) {
            $this->parameters[strtolower($parameter)] = strtolower($value);
        }
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return $this->toString();
    }

    /**
     * @param bool $includeParameters - include parameters when matching content-type string (default: false)
     */
    public function is(ContentType $contentType, bool $includeParameters = false) : bool {
        return $includeParameters
            ? $this->toString() === $contentType->toString()
            : $this->mainType === $contentType->mainType && $this->subType === $contentType->subType;
    }

    public function isJson() : bool {
        return $this->subType === 'json' || StringUtil::endsWith($this->subType, '+json');
    }

    public function isXml() : bool {
        return $this->subType === 'xml';
    }

    public function isPlainText() : bool {
        return $this->mainType === 'text' && $this->subType === 'plain';
    }

    public function isStream() : bool {
        return $this->mainType === 'application' && $this->subType === 'octet-stream';
    }

    /**
     * Return the main part of content-type (ex: text)
     */
    public function toMainType() : string {
        return $this->mainType;
    }

    /**
     * Return the sub part of content-type (ex: xml)
     */
    public function toSubType() : string {
        return $this->subType;
    }

    /**
     * Return an entire content-type string with parameters (ex: application/json; charset=latin)
     */
    public function toString() : string {
        $stringBuilder = ["{$this->mainType}/{$this->subType}"];
        foreach($this->parameters as $parameter => $value) {
            $stringBuilder[] = "{$parameter}={$value}";
        }
        return implode('; ', $stringBuilder);
    }
}
