<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandSummary;
use PHPUnit\Framework\TestCase;

class EmptyUnaryOperandSummaryTest extends TestCase {

    public static function dataProvider() : array {
        return [
            ['foo'],
            [1],
            [3.14],
            [true],
            [new \stdClass()],
            [STDOUT],
            [[1,2,3]],
            [null]
        ];
    }

    /**
     * @param mixed $actual
     * @dataProvider dataProvider
     */
    public function testToString(mixed $actual) {
        $message = new EmptyUnaryOperandSummary($actual);
        $expected = sprintf(
            'asserting type "%s" is empty',
            strtolower(gettype($actual))
        );
        $this->assertSame($expected, $message->toString());
    }

    /**
     * @param mixed $actual
     * @dataProvider dataProvider
     */
    public function testToNotString(mixed $actual) {
        $message = new EmptyUnaryOperandSummary($actual);
        $expected = sprintf(
            'asserting type "%s" is not empty',
            strtolower(gettype($actual))
        );
        $this->assertSame($expected, $message->toNotString());
    }

}