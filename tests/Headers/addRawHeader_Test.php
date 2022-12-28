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
namespace modethirteen\Http\Tests\Headers;

use InvalidArgumentException;
use modethirteen\Http\Headers;
use modethirteen\Http\Tests\PlugTestCase;

class addRawHeader_Test extends PlugTestCase {

    /**
     * @test
     */
    public function Can_set_header() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('X-Foo-bar: qux fred quxx');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', ['qux fred quxx'], $headers->toArray());
    }

    /**
     * @test
     */
    public function Can_handle_header_without_value() : void {

        // arrange
        $headers = new Headers();

        // act
        $exceptionThrown = false;
        try {
            $headers->addRawHeader('HTTP/1.1 400 BAD REQUEST');
        } catch(InvalidArgumentException) {
            $exceptionThrown = true;
        }

        // assert
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @test
     */
    public function Can_set_multi_value_header() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('X-Foo-bar: qux, fred, quxx; foo');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', ['qux, fred, quxx; foo'], $headers->toArray());
    }

    /**
     * @test
     */
    public function Can_add_header() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('X-Foo-bar: qux, fred, quxx; foo');
        $headers->addRawHeader('X-Foo-bar: a, b');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', ['qux, fred, quxx; foo', 'a, b'], $headers->toArray());
    }

    /**
     * @test
     */
    public function Can_add_header_with_comma_separation_enabled() : void {

        // arrange
        $headers = (new Headers())
            ->withRawHeaderCommaSeparationEnabled()
            ->toMutableHeaders();

        // act
        $headers->addRawHeader('X-Foo-bar: qux, fred, quxx; foo');
        $headers->addRawHeader('X-Foo-bar: a, b');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', ['qux', 'fred', 'quxx; foo', 'a', 'b'], $headers->toArray());
    }

    /**
     * @test
     */
    public function Can_set_empty_value() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('X-Foo-bar:');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', [''], $headers->toArray());
    }

    /**
     * @test
     */
    public function Can_only_set_a_single_empty_value() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('X-Foo-bar:');
        $headers->addRawHeader('X-Foo-bar:');
        $headers->addRawHeader('X-Foo-bar:');

        // assert
        $this->assertArrayHasKeyValue('X-Foo-Bar', [''], $headers->toArray());
    }

    /**
     * @test
     */
    public function Single_value_only_header_constraints_are_ignored_by_default_when_adding_raw_headers() : void {

        // arrange
        $headers = new Headers();

        // act
        $headers->addRawHeader('Content-Type: application/xml, application/json');
        $headers->addRawHeader('Location: https://example.com/foo, http://bar.example.com');

        // assert
        $headers = $headers->toArray();
        $this->assertArrayHasKeyValue('Content-Type', ['application/xml, application/json'], $headers);
        $this->assertArrayHasKeyValue('Location', ['https://example.com/foo, http://bar.example.com'], $headers);
    }

    /**
     * @test
     */
    public function Single_value_only_header_constraints_are_used_with_raw_header_comma_parsing_enabled() : void {

        // arrange
        $headers = (new Headers())
            ->withRawHeaderCommaSeparationEnabled()
            ->toMutableHeaders();

        // act
        $headers->addRawHeader('Content-Type: application/xml, application/json');
        $headers->addRawHeader('Location: https://example.com/foo, http://bar.example.com');

        // assert
        $headers = $headers->toArray();
        $this->assertArrayHasKeyValue('Content-Type', ['application/xml'], $headers);
        $this->assertArrayHasKeyValue('Location', ['https://example.com/foo'], $headers);
    }
}
