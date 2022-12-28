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
namespace modethirteen\Http\Tests\XUri;

use modethirteen\Http\Tests\PlugTestCase;
use modethirteen\Http\XUri;

class with_Test extends PlugTestCase {

    public static function param_expected_Provider() : array {
        return [
            ['bar', 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=bar#fragment'],
            [true, 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=true#fragment'],
            [false, 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=false#fragment'],
            [0, 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=0#fragment'],
            [-10, 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=-10#fragment'],
            [new class {
                public function __toString() : string {
                    return 'fred';
                }
            }, 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=fred#fragment'],
            [['qux', true, -10, 5], 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=qux%2Ctrue%2C-10%2C5#fragment'],
            [fn(): string => 'bazz', 'http://user:password@test.mindtouch.dev/?a=b&c=d&foo=bazz#fragment']
        ];
    }

    /**
     * @dataProvider param_expected_Provider
     * @test
     */
    public function With_add_query_parameter(mixed $param, string $expected) {

        // arrange
        $uriStr = 'http://user:password@test.mindtouch.dev/?a=b&c=d#fragment';

         // act
        $result = XUri::tryParse($uriStr)->with('foo', $param);

        // assert
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function Can_return_extended_instance() : void {

        // act
        $result = TestXUri::tryParse('http://user:password@test.mindtouch.dev/somepath?a=b&c=d&e=f#fragment')->with('foo', 'bar');

        // assert
        $this->assertInstanceOf(TestXUri::class, $result);
    }
}
