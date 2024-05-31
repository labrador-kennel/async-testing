<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\NullUnaryOperandSummary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NullUnaryOperandTest extends TestCase {

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

    #[DataProvider('dataProvider')]
    public function testToString(mixed $actual) : void {
        $message = new NullUnaryOperandSummary($actual);
        $expected = sprintf(
            'asserting type "%s" is null',
            strtolower(gettype($actual))
        );
        $this->assertSame($expected, $message->toString());
    }

    #[DataProvider('dataProvider')]
    public function testToNotString(mixed $actual) : void {
        $message = new NullUnaryOperandSummary($actual);
        $expected = sprintf(
            'asserting type "%s" is not null',
            strtolower(gettype($actual))
        );
        $this->assertSame($expected, $message->toNotString());
    }

}