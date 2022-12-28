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

use modethirteen\Http\Exception\JsonContentCannotSerializeArrayException;

/**
 * Class JsonContent
 *
 * @package modethirteen\Http\Content
 */
class JsonContent implements IContent, \Stringable {

    /**
     * Return an instance from a JSON encoded array
     *
     * @return static
     * @throws JsonContentCannotSerializeArrayException
     */
    public static function newFromArray(array $json) : object {
        $text = json_encode($json, JSON_THROW_ON_ERROR);
        if($text === false) {
            throw new JsonContentCannotSerializeArrayException($json);
        }
        return new static($text);
    }

    /**
     * @var ContentType|null
     */
    private $contentType;

    final public function __construct(private string $json) {
        $this->contentType = ContentType::newFromString(ContentType::JSON);
    }

    public function __clone() {

        // deep copy internal data objects and arrays
        $this->contentType = unserialize(serialize($this->contentType));
    }

    public function getContentType() : ?ContentType {
        return $this->contentType;
    }

    public function toRaw() : string {
        return $this->json;
    }

    public function toString() : string {
        return $this->json;
    }

    public function __toString() : string {
        return $this->toString();
    }
}