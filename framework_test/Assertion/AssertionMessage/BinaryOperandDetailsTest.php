<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use PHPUnit\Framework\TestCase;

class BinaryOperandDetailsTest extends TestCase {

    public function dataProvider() : array {
        return [
            ['string', 'bar'],
            ['integer', 1],
            ['float', 3.14],
            ['array', [1,2,3]],
            ['object', new \stdClass()],
            ['bool', true],
            ['null', null],
            ['resource', STDOUT]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToString(string $a, mixed $b) : void {
        $message = new BinaryOperandDetails($a, $b);
        $expected = sprintf(
            'comparing actual value %s equals %s',
            var_export($b, true),
            var_export($a, true)
        );
        $this->assertSame($expected, $message->toString());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToNotString(string $a, mixed $b) : void {
        $message = new BinaryOperandDetails($a, $b);
        $expected = sprintf(
            'comparing actual value %s does not equal %s',
            var_export($b, true),
            var_export($a, true)
        );
        $this->assertSame($expected, $message->toNotString());
    }
}