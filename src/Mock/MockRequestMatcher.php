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
namespace modethirteen\Http\Mock;

use InvalidArgumentException;
use modethirteen\Http\Content\IContent;
use modethirteen\Http\Headers;
use modethirteen\Http\IHeaders;
use modethirteen\Http\IMutableHeaders;
use modethirteen\Http\XUri;

/**
 * Class MockRequest
 *
 * Object for configuring a MockPlug request to mock or verify; file uploads not supported
 *
 * @package modethirteen\Http\Mock
 */
class MockRequestMatcher {

    /**
     * @var string[]
     */
    private static array $ignoredQueryParamNames = [];

    /**
     * @var string[]
     */
    private static array $ignoredHeaderNames = [];

    /**
     * Set query param names to ignore during matching
     *
     * @param string[] $names
     */
    public static function setIgnoredQueryParamNames(array $names) : void {
        self::$ignoredQueryParamNames = $names;
    }

    /**
     * Set HTTP header names to ignore during matching
     *
     * @param string[] $names
     */
    public static function setIgnoredHeaderNames(array $names) : void {
        self::$ignoredHeaderNames = $names;
    }

    /**
     * @var IMutableHeaders
     */
    private $headers;

    private ?string $body = null;

    public function __construct(private string $method, private XUri $uri) {
        $this->headers = new Headers();
    }

    public function __clone() {

        // deep copy internal data objects and arrays
        $this->uri = unserialize(serialize($this->uri));
        $this->headers = unserialize(serialize($this->headers));
    }

    /**
     * Retrieve HTTP method
     */
    public function getMethod() : string {
        return $this->method;
    }

    /**
     * Retrieve denormalized matcher uri
     */
    public function getUri() : XUri {
        return $this->uri;
    }

    /**
     * Retrieve HTTP headers
     */
    public function getHeaders() : IHeaders {
        return $this->headers;
    }

    /**
     * Retrieve HTTP message body
     */
    public function getBody() : ?string {
        return $this->body;
    }

    /**
     * Retrieve id to match mock results to matcher
     */
    public function getMatcherId() : string {
        $uri = $this->newNormalizedUriString();
        $headers = $this->newNormalizedHeaderStrings();
        return md5(serialize($headers) . "{$this->method}{$uri}{$this->body}");
    }

    /**
     * Return an instance with the specified HTTP headers.
     */
    public function withHeaders(IHeaders $headers) : MockRequestMatcher {
        $request = clone $this;
        $request->headers = $headers->toMutableHeaders();
        return $request;
    }

    /**
     * Return an instance with the specified HTTP headers added to existing mocked HTTP headers.
     */
    public function withAddedHeaders(IHeaders $headers) : MockRequestMatcher {
        $request = clone $this;
        $request->headers->addHeaders($headers);
        return $request;
    }

    /**
     * Return an instance with the specified body string
     *
     * @param string|string[]|null $body - array body is assumed to be form fields and will be encoded to a string
     */
    public function withBody($body) : MockRequestMatcher {
        if(is_string($body) || is_array($body) || $body == null) {
            if(is_array($body)) {
                $body = http_build_query($body);
            }
            $request = clone $this;
            $request->body = $body;
            return $request;
        }
        throw new InvalidArgumentException('Body value must be string, array, or null');
    }

    /**
     * Return an instance with the specified content. Method will set a body and content-type depending on
     * the value of the content object
     */
    public function withContent(IContent $content) : MockRequestMatcher {
        $request = clone $this;
        $contentType = $content->getContentType();
        $request->headers->setHeader(Headers::HEADER_CONTENT_TYPE, $contentType !== null ? $contentType->toString() : null);
        $request->body = $content->toString();
        return $request;
    }

    public function toArray() : array {
        return [
            'method' => $this->method,
            'uri' => $this->uri->toString(),
            'headers' => $this->headers->toFlattenedArray(),
            'body' => $this->body ?? ''
        ];
    }

    public function toNormalizedArray() : array {
        return [
            'method' => $this->method,
            'uri' => $this->newNormalizedUriString(),
            'headers' => $this->newNormalizedHeaderStrings(),
            'body' => $this->body ?? ''
        ];
    }

    private function newNormalizedUriString() : string {
        $params = [];
        $href = $this->uri->toString();

        // parse uri into components
        $data = parse_url($href);
        if(!is_array($data) || !isset($data['scheme']) || !isset($data['host'])) {

            // if for some outstanding reason, the uri is malformed, at least match on something
            return $href;
        }
        if(isset($data['query'])) {
            parse_str($data['query'], $params);
        }

        // filter parameters applied by Plug
        $params = array_diff_key($params, array_flip(self::$ignoredQueryParamNames));

        // rebuild uri
        $uri = $data['scheme'] . '://' . $data['host'];
        if(isset($data['port'])) {
            $uri .= ':' . $data['port'];
        }
        if(isset($data['path'])) {
            $uri .= $data['path'];
        }
        asort($params);
        if(!empty($params)) {
            $uri .= '?' . http_build_query($params);
        }
        return $uri;
    }

    /**
     * @return string[]
     */
    private function newNormalizedHeaderStrings() : array {
        $headers = $this->headers->toFlattenedArray();
        $headers = array_diff_key($headers, array_flip(self::$ignoredHeaderNames));

        // rebuild headers
        ksort($headers);
        return $headers;
    }
}
